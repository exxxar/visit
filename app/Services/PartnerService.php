<?php

namespace App\Services;

use App\Models\PartnerTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PartnerService
{

    /**
     * Комплексное начисление: прямой доход + MLM бонусы
     */
    public function rewardPartnerAndMlm(User $partner, float $orderAmount, int $requestId): void
    {
        DB::transaction(function () use ($partner, $orderAmount, $requestId) {
            $levelConfig = config('partners.levels')[$partner->partner_level] ?? config('partners.levels')[1];

            // 1. Прямая комиссия партнера за выполнение заказа
            $commission = round($orderAmount * ($levelConfig['commission_pct'] / 100), 2);

            $partner->increment('partner_balance', $commission);
            $partner->increment('completed_orders_count');
            $this->checkLevelUp($partner);

            PartnerTransaction::create([
                'user_id'     => $partner->id,
                'type'        => 'earning',
                'amount'      => $commission,
                'description' => "Комиссия {$levelConfig['commission_pct']}% за заказ #{$requestId}",
                'request_id'  => $requestId,
            ]);

            // 2. Распределение MLM-бонусов по 3 уровням (от суммы комиссии партнера)
            $this->distributeMlmBonuses($partner, $commission, $requestId);
        });

        $partner->refresh();
    }

    /**
     * Рекурсивное (циклическое) начисление бонусов до 3-го уровня
     */
    private function distributeMlmBonuses(User $earningPartner, float $commissionAmount, int $requestId): void
    {
        $mlmConfig = config('partners.mlm', ['level_1' => 5, 'level_2' => 3, 'level_3' => 1]);

        $currentReferrerId = $earningPartner->referred_by;
        $level = 1;

        while ($currentReferrerId && $level <= 3) {
            $referrer = User::find($currentReferrerId);
            if (!$referrer) break;

            // Проверяем, является ли реферер активным партнером или админом
            // (чтобы не начислять бонусы обычным клиентам, если они кого-то пригласили)
            $isPartner = $referrer->hasRole('admin')
                || $referrer->hasRole('ad_partner')
                || $referrer->hasRole('tech_partner')
                || in_array($referrer->meta['partner_type'] ?? '', ['ad', 'tech']);

            if ($isPartner) {
                $percentage = $mlmConfig["level_{$level}"] ?? 0;

                if ($percentage > 0) {
                    $mlmCommission = round($commissionAmount * ($percentage / 100), 2);

                    // Начисляем баланс
                    $referrer->increment('partner_balance', $mlmCommission);

                    // Логируем операцию
                    PartnerTransaction::create([
                        'user_id'     => $referrer->id,
                        'type'        => 'mlm',
                        'amount'      => $mlmCommission,
                        'description' => "Сетевой бонус {$level} ур. ({$percentage}%): от заказа #{$requestId} ({$earningPartner->name})",
                        'request_id'  => $requestId,
                    ]);
                }
            }

            // Переходим на уровень выше
            $currentReferrerId = $referrer->referred_by;
            $level++;
        }
    }

    private function checkLevelUp(User $partner): void
    {
        $levels = config('partners.levels');
        $currentLevel = $partner->partner_level;
        $ordersCount = $partner->completed_orders_count;

        $newLevel = $currentLevel;
        foreach ($levels as $lvl => $data) {
            if ($ordersCount >= $data['min_orders'] && $lvl > $newLevel) {
                $newLevel = $lvl;
            }
        }

        if ($newLevel > $currentLevel) {
            $partner->update(['partner_level' => $newLevel]);

            $bonus = 1000;
            $partner->increment('partner_balance', $bonus);
            PartnerTransaction::create([
                'user_id'     => $partner->id,
                'type'        => 'bonus',
                'amount'      => $bonus,
                'description' => "Бонус за достижение уровня: {$levels[$newLevel]['name']}",
            ]);
            $partner->refresh();
        }
    }

    /**
     * Начисление вознаграждения партнеру при завершении заказа
     */
    public function rewardPartner(User $partner, float $orderAmount, int $requestId): void
    {
        DB::transaction(function () use ($partner, $orderAmount, $requestId) {
            $level = config('partners.levels')[$partner->partner_level] ?? config('partners.levels')[1];
            $commission = round($orderAmount * ($level['commission_pct'] / 100), 2);

            // 1. Обновляем баланс и счетчик заказов
            $partner->increment('partner_balance', $commission);
            $partner->increment('completed_orders_count');

            // 2. Проверяем, не пора ли повысить уровень
            $this->checkLevelUp($partner);

            // 3. Создаем запись в истории
            PartnerTransaction::create([
                'user_id'      => $partner->id,
                'type'         => 'earning',
                'amount'       => $commission,
                'description'  => "Комиссия {$level['commission_pct']}% за заказ #{$requestId}",
                'request_id'   => $requestId,
            ]);
        });
        $partner->refresh();
    }



    /**
     * Создание заявки на вывод средств
     */
    public function requestWithdrawal(User $user, float $amount, string $paymentDetails): bool
    {
        if ($user->partner_balance < $amount || $amount <= 0) {
            return false;
        }

        DB::transaction(function () use ($user, $amount, $paymentDetails) {
            $user->decrement('partner_balance', $amount);

            \App\Models\PartnerWithdrawal::create([
                'user_id'         => $user->id,
                'amount'          => $amount,
                'payment_details' => $paymentDetails,
            ]);

            PartnerTransaction::create([
                'user_id'     => $user->id,
                'type'        => 'withdrawal',
                'amount'      => $amount,
                'description' => 'Заявка на вывод средств',
            ]);
        });

        return true;
    }
}
