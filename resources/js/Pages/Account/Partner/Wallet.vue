<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'


const props = defineProps([
    'balance', 'level', 'nextLevel', 'ordersCount',
    'transactions', 'withdrawals',
    'referrals', // <-- НОВОЕ: список приглашенных
    'mlmEarningsTotal' // <-- НОВОЕ: общий сетевой доход
])

const showWithdrawForm = ref(false)
const withdrawForm = ref({ amount: '', payment_details: '' })

const submitWithdraw = () => {
    router.post('/account/partner/withdraw', withdrawForm.value, {
        onSuccess: () => {
            showWithdrawForm.value = false
            withdrawForm.value = { amount: '', payment_details: '' }
        }
    })
}

const fmtMoney = (n) => new Intl.NumberFormat('ru-RU').format(n) + ' ₽'
const fmtDate = (d) => new Date(d).toLocaleDateString('ru-RU')

const typeLabel = {
    earning: { text: 'Начисление', color: 'var(--lime)' },
    withdrawal: { text: 'Вывод', color: 'var(--rose)' },
    bonus: { text: 'Бонус', color: 'var(--cyan)' },
}
</script>

<template>
    <div>
        <div class="pg-head"><h1>💼 Кошелек партнера</h1></div>

        <!-- Верхняя панель: Баланс, Уровень и Сетевой доход -->
        <div class="wallet-hero">
            <div class="wallet-balance">
                <span class="label">Доступно к выводу</span>
                <b class="amount">{{ fmtMoney(balance) }}</b>
                <VButton v-if="balance >= 100" size="sm" @click="showWithdrawForm = true" style="margin-top:12px">
                    Запросить вывод
                </VButton>
            </div>

            <div class="wallet-level">
                <div class="level-badge">🏆 {{ level.name }}</div>
                <p class="level-desc">Ваша комиссия: <b style="color:var(--lime)">{{ level.commission_pct }}%</b></p>

                <div v-if="nextLevel" class="progress-wrap">
                    <div class="progress-info">
                        <span>До уровня "{{ nextLevel.name }}"</span>
                        <span>{{ ordersCount }} / {{ nextLevel.min_orders }} заказов</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" :style="{ width: Math.min(100, (ordersCount / nextLevel.min_orders) * 100) + '%' }"></div>
                    </div>
                </div>
            </div>

            <!-- НОВЫЙ БЛОК: Сетевой доход -->
            <div class="wallet-mlm">
                <div class="mlm-badge">🌐 Сеть</div>
                <b class="mlm-amount">{{ fmtMoney(mlmEarningsTotal || 0) }}</b>
                <p class="mlm-desc">Заработано с команды (5% → 3% → 1%)</p>
                <div class="mlm-stats">
                    <span>В команде: <b>{{ referrals?.length || 0 }}</b></span>
                </div>
            </div>
        </div>

        <!-- Форма вывода -->
        <div v-if="showWithdrawForm" class="vcard" style="margin-top:18px; background:rgba(34,211,238,.05); border-color:rgba(34,211,238,.2)">
            <h3 style="margin-top:0">Заявка на вывод средств</h3>
            <div class="formgrid" style="margin-top:14px">
                <div>
                    <label class="lbl">Сумма (мин. 100 ₽)</label>
                    <input v-model.number="withdrawForm.amount" type="number" class="inp" placeholder="1000">
                </div>
                <div>
                    <label class="lbl">Реквизиты для оплаты</label>
                    <input v-model="withdrawForm.payment_details" class="inp" placeholder="Номер карты / Телефон / Crypto">
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px">
                <VButton @click="submitWithdraw">Отправить заявку</VButton>
                <VButton variant="ghost" @click="showWithdrawForm = false">Отмена</VButton>
            </div>
        </div>

        <!-- Две колонки: История и Последние выводы -->
        <div class="grid grid--2" style="margin-top:24px">
            <!-- История операций -->
            <div class="vcard">
                <h3 style="margin-top:0">История операций</h3>
                <div v-if="!transactions.data.length" style="color:var(--mut);text-align:center;padding:20px">
                    Операций пока нет
                </div>
                <div v-for="t in transactions.data" :key="t.id" class="tx-row">
                    <div class="tx-ico" :style="{ color: typeLabel[t.type].color }">
                        {{ t.type === 'earning' ? '💰' : t.type === 'withdrawal' ? '💸' : '🎁' }}
                    </div>
                    <div class="tx-info">
                        <b>{{ typeLabel[t.type].text }}</b>
                        <small>{{ t.description }}</small>
                    </div>
                    <div class="tx-amount" :class="t.type === 'withdrawal' ? 'negative' : 'positive'">
                        {{ t.type === 'withdrawal' ? '-' : '+' }}{{ fmtMoney(t.amount) }}
                    </div>
                </div>

                <!-- Пагинация -->
                <div class="pager" style="margin-top:16px">
                    <Link v-for="l in transactions.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                          :class="{ on: l.active, off: !l.url }" v-html="l.label" />
                </div>
            </div>

            <!-- НОВАЯ КОЛОНКА: Моя команда (Уровень 1) -->
            <div class="vcard">
                <h3 style="margin-top:0">Мои рефералы (1 уровень)</h3>
                <div v-if="!referrals || !referrals.length" style="color:var(--mut);text-align:center;padding:20px">
                    Вы еще никого не пригласили.<br>
                    <small>Поделитесь своей реферальной ссылкой!</small>
                </div>
                <div v-for="ref in referrals" :key="ref.id" class="ref-row">
                    <div class="ref-avatar">{{ (ref.name || 'U')[0].toUpperCase() }}</div>
                    <div class="ref-info">
                        <b>{{ ref.name || 'Пользователь' }}</b>
                        <small>Присоединился: {{ fmtDate(ref.created_at) }}</small>
                    </div>
                    <div class="ref-status" :class="ref.partner_type ? 'active' : 'pending'">
                        {{ ref.partner_type ? 'Партнер' : 'Ожидает' }}
                    </div>
                </div>
            </div>

            <!-- Статус выводов -->
            <div class="vcard">
                <h3 style="margin-top:0">Последние заявки на вывод</h3>
                <div v-if="!withdrawals.length" style="color:var(--mut);text-align:center;padding:20px">
                    Заявок на вывод нет
                </div>
                <div v-for="w in withdrawals" :key="w.id" class="tx-row">
                    <div class="tx-ico" :style="{ color: w.status === 'approved' ? 'var(--lime)' : w.status === 'rejected' ? 'var(--rose)' : 'var(--yellow)' }">
                        {{ w.status === 'approved' ? '✅' : w.status === 'rejected' ? '❌' : '⏳' }}
                    </div>
                    <div class="tx-info">
                        <b>Вывод средств</b>
                        <small>{{ fmtDate(w.created_at) }} · {{ w.payment_details }}</small>
                    </div>
                    <div class="tx-amount negative">-{{ fmtMoney(w.amount) }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.wallet-hero {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 18px;
}
.wallet-balance, .wallet-level {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 24px;
}
.wallet-balance .label { color: var(--mut); font-size: 13px; text-transform: uppercase; letter-spacing: .05em; }
.wallet-balance .amount {
    display: block;
    font-family: var(--disp);
    font-size: 42px;
    font-weight: 800;
    color: var(--txt);
    margin: 8px 0;
}
.level-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 99px;
    background: linear-gradient(90deg, var(--cyan), var(--violet));
    color: #fff;
    font-weight: 800;
    font-size: 14px;
    margin-bottom: 8px;
}
.level-desc { color: var(--mut); font-size: 14px; margin: 0 0 16px; }
.progress-wrap { margin-top: 12px; }
.progress-info {
    display: flex; justify-content: space-between;
    font-size: 12px; color: var(--mut); margin-bottom: 6px;
}
.progress-bar {
    height: 8px; background: var(--panel2); border-radius: 99px; overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--cyan), var(--lime));
    border-radius: 99px;
    transition: width 0.5s ease;
}

/* История операций */
.tx-row {
    display: flex; align-items: center; gap: 14px;
    padding: 12px 0; border-bottom: 1px solid var(--line);
}
.tx-row:last-child { border-bottom: none; }
.tx-ico { font-size: 20px; width: 36px; text-align: center; }
.tx-info { flex: 1; }
.tx-info b { display: block; font-size: 14px; color: var(--txt); }
.tx-info small { color: var(--mut); font-size: 12px; }
.tx-amount { font-family: var(--disp); font-weight: 700; font-size: 15px; }
.tx-amount.positive { color: var(--lime); }
.tx-amount.negative { color: var(--rose); }

@media (max-width: 768px) {
    .wallet-hero { grid-template-columns: 1fr; }
}

.wallet-hero {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr; /* Было 2, стало 3 колонки */
    gap: 18px;
}

.wallet-mlm {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.mlm-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 99px;
    background: linear-gradient(90deg, var(--orange), var(--magenta));
    color: #fff;
    font-weight: 800;
    font-size: 14px;
    margin-bottom: 8px;
    width: fit-content;
}

.mlm-amount {
    font-family: var(--disp);
    font-size: 32px;
    font-weight: 800;
    color: var(--txt);
    margin: 8px 0;
}

.mlm-desc {
    color: var(--mut);
    font-size: 13px;
    margin: 0 0 12px;
}

.mlm-stats {
    font-size: 13px;
    color: var(--mut);
    padding-top: 12px;
    border-top: 1px solid var(--line);
}
.mlm-stats b { color: var(--cyan); }

/* Стили для списка рефералов */
.ref-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 0; border-bottom: 1px solid var(--line);
}
.ref-row:last-child { border-bottom: none; }
.ref-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    display: grid; place-items: center;
    background: linear-gradient(135deg, var(--cyan), var(--violet));
    color: #fff; font-weight: 800; font-size: 14px; flex-shrink: 0;
}
.ref-info { flex: 1; }
.ref-info b { display: block; font-size: 14px; color: var(--txt); }
.ref-info small { color: var(--mut); font-size: 12px; }
.ref-status {
    font-size: 11px; font-weight: 700; padding: 4px 10px;
    border-radius: 99px; text-transform: uppercase;
}
.ref-status.active { background: rgba(163, 230, 53, 0.15); color: var(--lime); }
.ref-status.pending { background: rgba(250, 204, 21, 0.15); color: var(--yellow); }

@media (max-width: 900px) {
    .wallet-hero { grid-template-columns: 1fr; }
}
</style>
