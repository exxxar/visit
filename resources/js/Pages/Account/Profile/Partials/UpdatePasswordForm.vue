<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const passwordInput = ref(null)
const currentPasswordInput = ref(null)

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation')
                passwordInput.value?.focus()
            }
            if (form.errors.current_password) {
                form.reset('current_password')
                currentPasswordInput.value?.focus()
            }
        },
    })
}
</script>

<template>
    <section>
        <header class="section-header">
            <h2>🔒 Обновление пароля</h2>
            <p>Используйте длинный случайный пароль, чтобы ваш аккаунт оставался в безопасности</p>
        </header>

        <form @submit.prevent="updatePassword" class="profile-form">
            <div class="form-field">
                <label for="current_password" class="form-label">Текущий пароль</label>
                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="form-input"
                    autocomplete="current-password"
                />
                <p v-if="form.errors.current_password" class="form-error">{{ form.errors.current_password }}</p>
            </div>

            <div class="form-field">
                <label for="password" class="form-label">Новый пароль</label>
                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="form-input"
                    autocomplete="new-password"
                />
                <p v-if="form.errors.password" class="form-error">{{ form.errors.password }}</p>
            </div>

            <div class="form-field">
                <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="form-input"
                    autocomplete="new-password"
                />
                <p v-if="form.errors.password_confirmation" class="form-error">{{ form.errors.password_confirmation }}</p>
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
