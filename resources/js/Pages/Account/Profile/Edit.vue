<script setup>
// AccountLayout подключается автоматически через app.js по префиксу "Account/"
import DeleteUserForm from './Partials/DeleteUserForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'
import { Head } from '@inertiajs/vue3'

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
</script>

<template>
    <Head title="Профиль" />

    <div>
        <div class="pg-head">
            <h1>️ Настройки профиля</h1>
        </div>

        <div style="display:grid;grid-template-columns:1fr;gap:16px;max-width:600px">
            <!-- Карточка 1: Основная информация -->
            <div class="profile-card">

                <div class="profile-card__body">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>
            </div>

            <!-- Карточка 2: Пароль -->
            <div class="profile-card">

                <div class="profile-card__body">
                    <UpdatePasswordForm />
                </div>
            </div>

            <!-- Карточка 3: Удаление аккаунта -->
            <div class="profile-card profile-card--danger">

                <div class="profile-card__body">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.pg-head {
    margin-bottom: 24px;
}

.pg-head h1 {
    font-family: var(--disp);
    font-size: clamp(22px, 3vw, 28px);
    font-weight: 800;
    color: var(--txt);
    margin: 0;
}

.profile-card {
    border: 1px solid var(--line);
    border-radius: 14px;
    overflow: hidden;
    background: var(--panel);
    transition: .2s;
}

.profile-card:hover {
    border-color: var(--cyan);
}

.profile-card--danger {
    border-color: rgba(255, 92, 122, 0.3);
}

.profile-card--danger:hover {
    border-color: var(--rose);
}

.profile-card__head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--line);
    background: rgba(255, 255, 255, 0.02);
}

.profile-card__head h3 {
    margin: 0 0 4px;
    font-size: 16px;
    font-weight: 700;
    color: var(--txt);
}

.profile-card__head p {
    margin: 0;
    color: var(--mut);
    font-size: 13px;
    line-height: 1.4;
}

.profile-card__body {
    padding: 20px;
}

/* Адаптив для мобильных */
@media (max-width: 640px) {
    .profile-card__head {
        padding: 14px 16px;
    }
    .profile-card__body {
        padding: 16px;
    }
}
</style>
