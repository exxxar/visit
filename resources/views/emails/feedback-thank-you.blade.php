<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#0b101d;font-family:Arial,Helvetica,sans-serif;color:#eef1fb">
<div style="max-width:600px;margin:0 auto;padding:40px 20px">

    {{-- Шапка --}}
    <div style="text-align:center;padding:24px 0;border-bottom:1px solid rgba(255,255,255,.1)">
        <h1 style="margin:0;font-size:24px;letter-spacing:2px;color:#22d3ee">
            ВИЗИТ <span style="color:#8b5cf6">ДОНЕЦК</span>
        </h1>
    </div>

    {{-- Контент --}}
    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:32px;margin-top:24px">
        <h2 style="margin:0 0 16px;font-size:20px;color:#fff">
            Спасибо, {{ $feedback->name }}! ✨
        </h2>
        <p style="line-height:1.7;color:#c9d4ee;font-size:15px;margin:0 0 12px">
            Ваше сообщение успешно доставлено нашей команде. Мы внимательно его изучим и свяжемся с вами в течение рабочего дня.
        </p>

        {{-- Копия сообщения --}}
        <div style="background:rgba(34,211,238,.06);border-left:3px solid #22d3ee;border-radius:8px;padding:16px 18px;margin:24px 0">
            <p style="margin:0 0 6px;color:#22d3ee;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px">
                Ваше обращение
            </p>
            <p style="margin:0 0 8px;color:#c9d4ee;font-size:13px">
                <strong style="color:#fff">Тема:</strong> {{ $feedback->subjectLabel() }}
            </p>
            <p style="margin:0;color:#c9d4ee;font-size:14px;line-height:1.6;white-space:pre-wrap">
                {{ $feedback->message }}
            </p>
        </div>

        <p style="line-height:1.6;color:#8a96b4;font-size:13px;margin:20px 0 0">
            💡 Если ваш вопрос срочный — позвоните нам по телефону, указанному на сайте, или напишите в наши соцсети.
        </p>
    </div>

    {{-- Подвал --}}
    <div style="text-align:center;padding:24px 0;color:#5a6684;font-size:12px">
        <p style="margin:8px 0">С уважением, команда ВИЗИТ ДОНЕЦК</p>
        <p style="margin:8px 0">визит-донецк.рф · Главный городской путеводитель</p>
    </div>
</div>
</body>
</html>
