<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3'

defineProps({
    mustVerifyEmail: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: '',
    },
})

const user = usePage().props.auth.user

const form = useForm({
    name: user.name,
    email: user.email,
})
</script>

<template>
    <section>
        <header class="section-header">
            <h2>👤 Основная информация</h2>
            <p>Обновите имя и email-адрес вашего аккаунта</p>
        </header>

        <form @submit.prevent="form.patch(route('account-users.profile.update'))" class="profile-form">
            <div class="form-field">
                <label for="name" class="form-label">Имя</label>
                <input
                    id="name"
                    type="text"
                    class="form-input"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
            </div>

            <div class="form-field">
                <label for="email" class="form-label">Email</label>
                <input
                    id="email"
                    type="email"
                    class="form-input"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <p v-if="form.errors.email" class="form-error">{{ form.errors.email }}</p>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="verify-block">
                <p class="verify-text">
                    Ваш email не подтверждён.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="verify-link"
                    >
                        Нажмите, чтобы отправить письмо повторно
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="verify-success"
                >
                    ✓ Новое письмо отправлено на ваш email
                </div>
            </div>

            <div class="form-actions">
                <button class="btn-grad" type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Сохранение...' : 'Сохранить' }}
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="form-success">✓ Сохранено</p>
                </Transition>
            </div>
        </form>
    </section>
</template>

<style scoped>
.section-header h2 {
    margin: 0 0 6px;
    font-size: 16px;
    font-weight: 700;
    color: var(--txt);
}

.section-header p {
    margin: 0;
    font-size: 13px;
    color: var(--mut);
    line-height: 1.5;
}

.profile-form {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--mut);
    text-transform: uppercase;
    letter-spacing: .06em;
}

.form-input {
    width: 100%;
    padding: 11px 14px;
    background: var(--bg2);
    border: 1px solid var(--line);
    border-radius: 10px;
    color: var(--txt);
    font-size: 14px;
    font-family: var(--body);
    outline: none;
    transition: .2s;
    box-sizing: border-box;
}

.form-input:focus {
    border-color: var(--cyan);
    box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.15);
}

.form-error {
    margin: 0;
    font-size: 12px;
    color: var(--rose);
}

.verify-block {
    padding: 12px 14px;
    background: rgba(250, 204, 21, 0.08);
    border: 1px solid rgba(250, 204, 21, 0.25);
    border-radius: 10px;
}

.verify-text {
    margin: 0;
    font-size: 13px;
    color: var(--txt);
}

.verify-link {
    background: none;
    border: none;
    padding: 0;
    color: var(--cyan);
    font-weight: 700;
    cursor: pointer;
    text-decoration: underline;
    font-size: 13px;
}

.verify-link:hover {
    color: var(--violet);
}

.verify-success {
    margin-top: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--cyan);
}

.form-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-top: 4px;
}

.btn-grad {
    padding: 10px 22px;
    background: linear-gradient(135deg, var(--cyan), var(--violet));
    border: none;
    border-radius: 10px;
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: .2s;
}

.btn-grad:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(34, 211, 238, 0.3);
}

.btn-grad:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.form-success {
    margin: 0;
    font-size: 13px;
    color: var(--cyan);
    font-weight: 600;
}
</style>
