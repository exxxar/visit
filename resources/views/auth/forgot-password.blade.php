<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Восстановление пароля | ВИЗИТ ДОНЕЦК</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css'])
</head>
<body>
<main style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px">
    <div class="glass" style="padding:32px;max-width:420px;width:100%;border-radius:20px">
        <h1 style="font-size:24px;margin-top:0">🔑 Восстановление пароля</h1>
        <p style="color:var(--mut);font-size:14px">
            Укажите email, на который зарегистрирован аккаунт — мы отправим ссылку для сброса пароля.
        </p>

        @if(session('success'))
            <div style="background:rgba(34,211,238,.12);border:1px solid rgba(34,211,238,.3);color:#22d3ee;padding:12px;border-radius:10px;margin:12px 0;font-size:13px">
                {{ session('success') }}
            </div>
        @endif

        @error('email')
        <div style="background:rgba(244,63,94,.12);border:1px solid rgba(244,63,94,.3);color:#fb7185;padding:12px;border-radius:10px;margin:12px 0;font-size:13px">
            {{ $message }}
        </div>
        @enderror

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label class="lbl">Email</label>
            <input type="email" name="email" class="inp" value="{{ old('email') }}" required autofocus>
            <button type="submit" class="btn btn-grad" style="width:100%;margin-top:16px">Отправить ссылку</button>
        </form>

        <p style="text-align:center;margin-top:16px;font-size:13px">
            <a href="/login" style="color:var(--cyan)">← Вернуться ко входу</a>
        </p>
    </div>
</main>
</body>
</html>
