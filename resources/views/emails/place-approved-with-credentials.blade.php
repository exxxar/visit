<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#0b101d;font-family:Arial,Helvetica,sans-serif;color:#eef1fb">
<div style="max-width:600px;margin:0 auto;padding:40px 20px">

    {{-- Шапка --}}
    <div style="text-align:center;padding:24px 0;border-bottom:1px solid rgba(255,255,255,.1)">
        <h1 style="margin:0;font-size:24px;letter-spacing:2px;color:#22d3ee">ВИЗИТ <span style="color:#8b5cf6">ДОНЕЦК</span></h1>
    </div>

    {{-- Контент --}}
    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:32px;margin-top:24px">
        <h2 style="margin:0 0 16px;font-size:20px;color:#fff">🎉 Поздравляем, {{ $owner->name }}!</h2>
        <p style="line-height:1.7;color:#c9d4ee;font-size:15px;margin:0 0 12px">
            Ваше заведение <strong style="color:#22d3ee">«{{ $place->name }}»</strong> прошло модерацию и опубликовано на портале ВИЗИТ ДОНЕЦК.
        </p>

        {{-- Данные для входа --}}
        <div style="background:rgba(34,211,238,.08);border:1px solid rgba(34,211,238,.25);border-radius:12px;padding:24px;margin:24px 0">
            <p style="margin:0 0 16px;color:#22d3ee;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:1px">
                🔑 Доступ к личному кабинету
            </p>
            <table style="width:100%;border-collapse:collapse">
                <tr>
                    <td style="padding:8px 0;color:#8a96b4;font-size:14px;width:120px">Логин:</td>
                    <td style="padding:8px 0;color:#fff;font-size:15px;font-weight:600">{{ $owner->email }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;color:#8a96b4;font-size:14px">Пароль:</td>
                    <td style="padding:8px 0;color:#fff;font-size:15px;font-weight:600;font-family:monospace;background:rgba(0,0,0,.3);border-radius:6px;padding:8px 12px">{{ $password }}</td>
                </tr>
            </table>
            <p style="margin:16px 0 0;color:#8a96b4;font-size:13px">
                ⚠️ Рекомендуем сменить пароль после первого входа в настройках профиля.
            </p>
        </div>

        {{-- Кнопки --}}
        <div style="text-align:center;margin:28px 0">
            <a href="{{ url('/login') }}"
               style="display:inline-block;padding:14px 32px;background:linear-gradient(90deg,#22d3ee,#8b5cf6);color:#fff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;margin-right:12px">
                Войти в кабинет
            </a>
            <a href="{{ url('/place/' . $place->slug) }}"
               style="display:inline-block;padding:14px 32px;background:transparent;border:1px solid rgba(255,255,255,.2);color:#fff;text-decoration:none;border-radius:12px;font-weight:600;font-size:15px">
                Карточка заведения
            </a>
        </div>

        {{-- Что можно делать в кабинете --}}
        <div style="background:rgba(139,92,246,.08);border:1px solid rgba(139,92,246,.2);border-radius:12px;padding:20px;margin-top:20px">
            <p style="margin:0 0 12px;color:#c9d4ee;font-size:14px"><strong style="color:#8b5cf6">В личном кабинете вы можете:</strong></p>
            <ul style="margin:0;padding-left:20px;color:#c9d4ee;font-size:14px;line-height:1.8">
                <li>Редактировать карточку заведения</li>
                <li>Добавлять фотографии и новости</li>
                <li>Смотреть аналитику просмотров</li>
                <li>Отвечать на отзывы посетителей</li>
            </ul>
        </div>
    </div>

    {{-- Подвал --}}
    <div style="text-align:center;padding:24px 0;color:#5a6684;font-size:12px">
        <p style="margin:8px 0">С уважением, команда ВИЗИТ ДОНЕЦК</p>
        <p style="margin:8px 0">визит-донецк.рф · Главный городской путеводитель</p>
    </div>
</div>
</body>
</html>
