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
            Ваше заведение <strong style="color:#22d3ee">«{{ $place->name }}»</strong> прошло модерацию и теперь опубликовано на портале ВИЗИТ ДОНЕЦК.
        </p>
        <p style="line-height:1.7;color:#c9d4ee;font-size:15px;margin:0 0 24px">
            Карточка видна всем посетителям сайта: в каталоге, на карте города и в поиске.
        </p>

        {{-- Кнопка --}}
        <div style="text-align:center;margin:28px 0">
            <a href="{{ url('/place/' . $place->slug) }}"
               style="display:inline-block;padding:14px 32px;background:linear-gradient(90deg,#22d3ee,#8b5cf6);color:#fff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px">
                Посмотреть карточку заведения
            </a>
        </div>

        {{-- Инфо-блок --}}
        <div style="background:rgba(139,92,246,.08);border:1px solid rgba(139,92,246,.2);border-radius:12px;padding:20px;margin-top:20px">
            <p style="margin:0 0 8px;color:#c9d4ee;font-size:14px"><strong style="color:#8b5cf6">💡 Совет:</strong></p>
            <p style="margin:0;color:#c9d4ee;font-size:14px;line-height:1.6">
                Войдите в личный кабинет, чтобы добавить фотографии, меню и новости заведения — карточки с полным профилем получают в 3 раза больше просмотров.
            </p>
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
