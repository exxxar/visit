<?php

namespace App\Services;

use App\Models\Feedback;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    /**
     * Отправляет уведомление об обращении в Телеграм.
     * Возвращает true при успехе, false если не настроен или ошибка.
     */
    public function sendFeedback(Feedback $feedback): bool
    {
        $token  = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (empty($token) || empty($chatId)) {
            Log::info('[TELEGRAM] Не настроен — пропускаем отправку');
            return false;
        }

        $text = $this->formatMessage($feedback);

        try {
            $response = Http::timeout(10)
                ->asJson()
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id'    => $chatId,
                    'text'       => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

            if ($response->ok() && $response->json('ok')) {
                Log::info('[TELEGRAM] ✓ Уведомление отправлено', ['feedback_id' => $feedback->id]);
                return true;
            }

            Log::warning('[TELEGRAM] Ошибка отправки', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 300),
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('[TELEGRAM] Exception при отправке', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected function formatMessage(Feedback $feedback): string
    {
        $subjectEmoji = match ($feedback->subject) {
            'general'     => '💬',
            'partnership' => '🤝',
            'ads'         => '📣',
            'bug'         => '🐛',
            'suggestion'  => '💡',
            'complaint'   => '⚠️',
            default       => '📌',
        };

        $adminUrl = url('/admin/feedback');
        $isEmail  = filter_var($feedback->contact, FILTER_VALIDATE_EMAIL);
        $contact  = $isEmail
            ? '<a href="mailto:' . $feedback->contact . '">' . $feedback->contact . '</a>'
            : '<a href="tel:' . preg_replace('/[^+\d]/', '', $feedback->contact) . '">' . $feedback->contact . '</a>';

        return implode("\n", [
            "{$subjectEmoji} <b>Новое обращение с сайта</b>",
            '',
            '👤 <b>Имя:</b> ' . e($feedback->name),
            '📞 <b>Контакт:</b> ' . $contact,
            '🏷 <b>Тема:</b> ' . e($feedback->subjectLabel()),
            '🌐 <b>IP:</b> ' . e($feedback->ip ?? '—'),
            '🕐 <b>Дата:</b> ' . $feedback->created_at->format('d.m.Y H:i'),
            '',
            '💬 <b>Сообщение:</b>',
            '<i>' . e($feedback->message) . '</i>',
            '',
            '👉 <a href="' . $adminUrl . '">Открыть в админке</a>',
        ]);
    }
}
