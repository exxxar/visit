<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Новый пароль | ВИЗИТ ДОНЕЦК</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css'])
</head>
<body>
<main style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px">
    <div class="glass" style="padding:32px;max-width:420px;width:100%;border-radius:20px">
        <h1 style="font-size:24px;margin-top:0">Задайте новый пароль</h1>

        @error('email')
        <div style="background:rgba(244,63,94,.12);border:1px solid rgba(244,63,94,.3);color:#fb7185;padding:12px;border-radius:10px;margin:12px 0;font-size:13px">
            {{ $message }}
        </div>
        @enderror

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <label class="lbl">Новый пароль</label>
            <input type="password" name="password" class="inp" required autofocus minlength="8">

            <label class="lbl" style="margin-top:12px">Повторите пароль</label>
            <input type="password" name="password_confirmation" class="inp" required minlength="8">

            <button type="submit" class="btn btn-grad" style="width:100%;margin-top:16px">Сохранить пароль</button>
        </form>
    </div>
</main>
</body>
</html>
