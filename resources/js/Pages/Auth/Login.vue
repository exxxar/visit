<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({ email: '', password: '', remember: false })
const submit = () => form.post('/login')
</script>

<template>
    <div class="auth">
        <div class="auth__card">
            <a href="/" class="side__logo">ВИЗИТ<em>ДОНЕЦК</em></a>
            <h1>Вход в кабинет</h1>

            <form @submit.prevent="submit">
                <label class="lbl">Email</label>
                <input v-model="form.email" type="email" class="inp" autofocus autocomplete="email">
                <p v-if="form.errors.email" class="err">{{ form.errors.email }}</p>

                <label class="lbl" style="margin-top:14px">Пароль</label>
                <input v-model="form.password" type="password" class="inp" autocomplete="current-password">
                <p v-if="form.errors.password" class="err">{{ form.errors.password }}</p>

                <label class="chk" style="margin-top:14px">
                    <input v-model="form.remember" type="checkbox"><i>✓</i><span>Запомнить меня</span>
                </label>

                <button class="vbtn vbtn--grad" :disabled="form.processing" style="width:100%;margin-top:16px">
                    Войти
                </button>
            </form>

            <p class="auth__foot">Нет аккаунта? <a href="/register">Зарегистрировать заведение</a></p>
            <p class="auth__demo">демо: admin@vizit-donetsk.ru · editor@… · owner@… / password</p>
        </div>
    </div>
</template>

<style scoped>
.auth{min-height:100vh;display:grid;place-items:center;padding:20px;
    background:radial-gradient(60% 60% at 50% 20%,rgba(79,125,255,.16),transparent),
    radial-gradient(40% 40% at 80% 70%,rgba(240,80,224,.1),transparent),#05070d}
.auth__card{width:min(430px,94%);border-radius:26px;border:1px solid rgba(255,255,255,.12);
    background:rgba(10,14,24,.85);backdrop-filter:blur(22px);padding:36px;
    box-shadow:0 40px 90px -30px rgba(0,0,0,.8)}
.auth__card h1{margin:20px 0 22px;font-size:22px}
.auth__foot{margin-top:18px;color:var(--mut);font-size:13.5px}
.auth__foot a{color:var(--cyan);font-weight:800}
.auth__demo{margin-top:10px;color:#525b76;font-size:12px}
</style>
