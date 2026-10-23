<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({ name: '', email: '', phone: '', password: '', password_confirmation: '' })
const submit = () => form.post('/register')
</script>

<template>
    <div class="auth">
        <div class="auth__card">
            <a href="/" class="side__logo">ВИЗИТ<em>ДОНЕЦК</em></a>
            <h1>Регистрация заведения</h1>
            <p class="auth__sub">Аккаунт владельца: карточка на сайте, новости и аналитика.</p>

            <form @submit.prevent="submit">
                <label class="lbl">Имя и фамилия</label>
                <input v-model="form.name" class="inp" autofocus>
                <p v-if="form.errors.name" class="err">{{ form.errors.name }}</p>

                <div class="formgrid" style="margin-top:12px">
                    <div>
                        <label class="lbl">Email</label>
                        <input v-model="form.email" type="email" class="inp">
                        <p v-if="form.errors.email" class="err">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="lbl">Телефон</label>
                        <input v-model="form.phone" type="tel" class="inp" placeholder="+7 ___ ___-__-__">
                    </div>
                </div>

                <div class="formgrid" style="margin-top:12px">
                    <div>
                        <label class="lbl">Пароль</label>
                        <input v-model="form.password" type="password" class="inp">
                        <p v-if="form.errors.password" class="err">{{ form.errors.password }}</p>
                    </div>
                    <div>
                        <label class="lbl">Повторите</label>
                        <input v-model="form.password_confirmation" type="password" class="inp">
                    </div>
                </div>

                <button class="vbtn vbtn--grad" :disabled="form.processing" style="width:100%;margin-top:20px">
                    Создать аккаунт
                </button>
            </form>

            <p class="auth__foot">Уже с нами? <a href="/login">Войти</a></p>
        </div>
    </div>
</template>

<style scoped>
.auth{min-height:100vh;display:grid;place-items:center;padding:20px;
    background:radial-gradient(60% 60% at 50% 20%,rgba(79,125,255,.16),transparent),
    radial-gradient(40% 40% at 20% 80%,rgba(163,230,53,.08),transparent),#05070d}
.auth__card{width:min(470px,94%);border-radius:26px;border:1px solid rgba(255,255,255,.12);
    background:rgba(10,14,24,.85);backdrop-filter:blur(22px);padding:36px;
    box-shadow:0 40px 90px -30px rgba(0,0,0,.8)}
.auth__card h1{margin:20px 0 6px;font-size:22px}
.auth__sub{color:var(--mut);font-size:13.5px;margin-bottom:20px}
.auth__foot{margin-top:18px;color:var(--mut);font-size:13.5px}
.auth__foot a{color:var(--cyan);font-weight:800}
</style>
