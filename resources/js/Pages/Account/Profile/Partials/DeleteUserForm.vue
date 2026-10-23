<script setup>
import { useForm } from '@inertiajs/vue3'
import { nextTick, ref } from 'vue'

const confirmingUserDeletion = ref(false)
const passwordInput = ref(null)

const form = useForm({
    password: '',
})

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true
    nextTick(() => passwordInput.value?.focus())
}

const deleteUser = () => {
    form.delete(route('account-users.profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    })
}

const closeModal = () => {
    confirmingUserDeletion.value = false
    form.reset()
}
</script>

<template>
    <section class="delete-section">
        <header class="delete-header">
            <h2>⚠️ Удаление аккаунта</h2>
            <p>После удаления аккаунта все его ресурсы и данные будут удалены безвозвратно. Перед удалением скачайте данные, которые хотите сохранить.</p>
        </header>

        <button class="btn-danger" @click="confirmUserDeletion">Удалить аккаунт</button>

        <!-- Модалка подтверждения -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="confirmingUserDeletion" class="modal-backdrop" @click.self="closeModal">
                    <div class="modal-box">
                        <h2>Вы уверены?</h2>
                        <p>Это действие нельзя отменить. Введите пароль для подтверждения удаления аккаунта.</p>

                        <div class="modal-field">
                            <input
                                id="delete-password"
                                ref="passwordInput"
                                v-model="form.password"
                                type="password"
                                class="modal-input"
                                placeholder="Ваш пароль"
                                @keyup.enter="deleteUser"
                            />
                            <p v-if="form.errors.password" class="modal-error">{{ form.errors.password }}</p>
                        </div>

                        <div class="modal-actions">
                            <button class="btn-ghost" @click="closeModal">Отмена</button>
                            <button
                                class="btn-danger"
                                :class="{ 'opacity-50': form.processing }"
                                :disabled="form.processing"
                                @click="deleteUser"
                            >
                                Удалить аккаунт
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </section>
</template>

<style scoped>
.delete-header h2 {
    margin: 0 0 6px;
    font-size: 16px;
    font-weight: 700;
    color: var(--txt);
}

.delete-header p {
    margin: 0;
    font-size: 13px;
    color: var(--mut);
    line-height: 1.5;
}

.btn-danger {
    margin-top: 16px;
    padding: 10px 20px;
    background: rgba(255, 92, 122, 0.15);
    border: 1px solid rgba(255, 92, 122, 0.4);
    border-radius: 10px;
    color: var(--rose);
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: .2s;
}

.btn-danger:hover {
    background: rgba(255, 92, 122, 0.25);
    border-color: var(--rose);
}

.btn-danger:disabled {
    opacity: .5;
    cursor: not-allowed;
}

/* Модалка */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(5, 7, 13, 0.75);
    backdrop-filter: blur(6px);
    display: grid;
    place-items: center;
    z-index: 1000;
    padding: 20px;
}

.modal-box {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 24px;
    max-width: 440px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.modal-box h2 {
    margin: 0 0 8px;
    font-size: 18px;
    font-weight: 700;
    color: var(--txt);
}

.modal-box p {
    margin: 0 0 20px;
    font-size: 13px;
    color: var(--mut);
    line-height: 1.5;
}

.modal-field {
    margin-bottom: 16px;
}

.modal-input {
    width: 100%;
    padding: 12px 14px;
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

.modal-input:focus {
    border-color: var(--rose);
    box-shadow: 0 0 0 3px rgba(255, 92, 122, 0.15);
}

.modal-error {
    margin: 6px 0 0;
    font-size: 12px;
    color: var(--rose);
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-ghost {
    padding: 10px 20px;
    background: transparent;
    border: 1px solid var(--line);
    border-radius: 10px;
    color: var(--mut);
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: .2s;
}

.btn-ghost:hover {
    border-color: var(--cyan);
    color: var(--txt);
}

/* Анимация */
.modal-enter-active,
.modal-leave-active {
    transition: opacity .25s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
