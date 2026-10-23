<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#0b101d;font-family:Arial,Helvetica,sans-serif;color:#eef1fb">
<div style="max-width:600px;margin:0 auto;padding:40px 20px">

    <div style="text-align:center;padding:24px 0;border-bottom:1px solid rgba(255,255,255,.1)">
        <h1 style="margin:0;font-size:24px;letter-spacing:2px;color:#22d3ee">
            ВИЗИТ <span style="color:#8b5cf6">ДОНЕЦК</span>
        </h1>
    </div>

    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:32px;margin-top:24px">
        <h2 style="margin:0 0 14px;font-size:20px;color:#fff">
            Здравствуйте, {{ $user->name }}! 🔑
        </h2>

        <p style="line-height:1.7;color:#c9d4ee;font-size:15px;margin:0 0 24px">
            Для вашего аккаунта был запрошен сброс пароля. Нажмите кнопку ниже, чтобы задать новый пароль. Ссылка действительна в течение 60 минут.
        </p>

        <div style="text-align:center;margin:28px 0">
            <a href="{{ $url }}"
               style="display:inline-block;padding:14px 32px;background:linear-gradient(90deg,#22d3ee,#8b5cf6);color:#fff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px">
                Сбросить пароль
            </a>
        </div>

        <p style="line-height:1.6;color:#8a96b4;font-size:12px;margin:20px 0 0">
            Если кнопка не работает, скопируйте ссылку в браузер:<br>
            <span style="color:#22d3ee;word-break:break-all">{{ $url }}</span>
        </p>

        <p style="line-height:1.6;color:#8a96b4;font-size:13px;margin-top:20px">
            ⚠ Если вы не запрашивали сброс пароля — просто проигнорируйте это письмо. Ваш текущий пароль останется без изменений.
        </p>
    </div>

    <div style="text-align:center;padding:24px 0;color:#5a6684;font-size:12px">
        <p style="margin:8px 0">С уважением, команда ВИЗИТ ДОНЕЦК</p>
        <p style="margin:8px 0">визит-донецк.рф</p>
    </div>
</div>
</body>
</html>
