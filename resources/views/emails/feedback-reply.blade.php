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
            Здравствуйте, {{ $feedback->name }}! 👋
        </h2>

        <p style="line-height:1.7;color:#c9d4ee;font-size:15px;margin:0 0 20px">
            Спасибо за ваше обращение по теме «{{ $feedback->subjectLabel() }}». Вот наш ответ:
        </p>

        <div style="background:rgba(34,211,238,.06);border-left:3px solid #22d3ee;border-radius:8px;padding:18px 20px;margin-bottom:24px">
            <p style="margin:0;color:#eef1fb;font-size:15px;line-height:1.7;white-space:pre-wrap">{{ $message }}</p>
        </div>

        <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:10px;padding:14px 18px">
            <p style="margin:0 0 8px;color:#5a6684;font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:700">
                Ваше обращение от {{ $feedback->created_at->format('d.m.Y') }}
            </p>
            <p style="margin:0;color:#8a96b4;font-size:13px;line-height:1.6;white-space:pre-wrap">{{ $feedback->message }}</p>
        </div>

        <p style="margin:24px 0 0;color:#c9d4ee;font-size:14px;line-height:1.6">
            С уважением,<br>
            <b style="color:#fff">Команда ВИЗИТ ДОНЕЦК</b><br>
            <span style="color:#5a6684;font-size:12px">визит-донецк.рф</span>
        </p>
    </div>

    <div style="text-align:center;padding:20px 0;color:#5a6684;font-size:11px">
        Просто ответьте на это письмо, если хотите продолжить диалог.
    </div>
</div>
</body>
</html>
