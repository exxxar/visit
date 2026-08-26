<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#0b101d;font-family:Arial,Helvetica,sans-serif;color:#eef1fb">
<div style="max-width:640px;margin:0 auto;padding:40px 20px">

    <div style="text-align:center;padding:20px 0;border-bottom:1px solid rgba(255,255,255,.1)">
        <h1 style="margin:0;font-size:22px;color:#f050e0">🔔 Новое обращение с сайта</h1>
    </div>

    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:28px;margin-top:24px">
        <table style="width:100%;border-collapse:collapse;font-size:14px">
            <tr>
                <td style="padding:10px 0;color:#8a96b4;width:130px;vertical-align:top">Имя</td>
                <td style="padding:10px 0;color:#fff;font-weight:600">{{ $feedback->name }}</td>
            </tr>
            <tr>
                <td style="padding:10px 0;color:#8a96b4;vertical-align:top">Контакт</td>
                <td style="padding:10px 0">
                    @if(filter_var($feedback->contact, FILTER_VALIDATE_EMAIL))
                        <a href="mailto:{{ $feedback->contact }}" style="color:#22d3ee;text-decoration:none">{{ $feedback->contact }}</a>
                    @else
                        <a href="tel:{{ preg_replace('/[^+\d]/', '', $feedback->contact) }}" style="color:#22d3ee;text-decoration:none">{{ $feedback->contact }}</a>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding:10px 0;color:#8a96b4;vertical-align:top">Тема</td>
                <td style="padding:10px 0">
                        <span style="display:inline-block;padding:4px 12px;background:rgba(240,80,224,.15);border:1px solid rgba(240,80,224,.3);border-radius:99px;color:#f050e0;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.05em">
                            {{ $feedback->subjectLabel() }}
                        </span>
                </td>
            </tr>
            <tr>
                <td style="padding:10px 0;color:#8a96b4;vertical-align:top">IP</td>
                <td style="padding:10px 0;color:#c9d4ee;font-size:13px">{{ $feedback->ip }}</td>
            </tr>
            <tr>
                <td style="padding:10px 0;color:#8a96b4;vertical-align:top">Дата</td>
                <td style="padding:10px 0;color:#c9d4ee;font-size:13px">{{ $feedback->created_at->format('d.m.Y H:i') }}</td>
            </tr>
        </table>

        {{-- Сообщение --}}
        <div style="background:rgba(139,92,246,.08);border-left:3px solid #8b5cf6;border-radius:8px;padding:18px;margin:20px 0">
            <p style="margin:0 0 8px;color:#8b5cf6;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px">
                Сообщение
            </p>
            <p style="margin:0;color:#eef1fb;font-size:15px;line-height:1.7;white-space:pre-wrap">
                {{ $feedback->message }}
            </p>
        </div>

        {{-- Кнопка --}}
        <div style="text-align:center;margin-top:24px">
            <a href="{{ url('/admin/feedback') }}"
               style="display:inline-block;padding:14px 32px;background:linear-gradient(90deg,#f050e0,#8b5cf6);color:#fff;text-decoration:none;border-radius:12px;font-weight:700;font-size:14px">
                Открыть в админке →
            </a>
        </div>
    </div>

    <div style="text-align:center;padding:20px 0;color:#5a6684;font-size:11px">
        Это автоматическое уведомление портала визит-донецк.рф
    </div>
</div>
</body>
</html>
