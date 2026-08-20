<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ВИЗИТ ДОНЕЦК — главный путеводитель по городу</title>
    <meta name="description" content="{{ $settings['hero']['sub'] ?? 'Все лучшие места Донецка — в одном путеводителе.' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700;800;900&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
</head>
<body>
@include('landing.header')
@include('landing.hero')          {{-- читает $settings --}}
@include('landing.partners')      {{-- статика --}}
@include('landing.quick')         {{-- статика --}}
@include('landing.bento')         {{-- статика с категориями --}}
@include('landing.popular')       {{-- цикл $popular --}}
@include('landing.moods')         {{-- статика --}}
@include('landing.districts')     {{-- оболочка, данные из payload --}}
@include('landing.map')           {{-- оболочка --}}
@include('landing.new')           {{-- цикл $newPlaces --}}
@include('landing.editorial')     {{-- цикл $posts --}}
@include('landing.events')        {{-- цикл $events --}}
@include('landing.print')         {{-- статика + viewer --}}
@include('landing.business')      {{-- статика + модалки --}}
@include('landing.nearby')
@include('landing.subscribe')
@include('landing.footer')
@include('landing.mnav')
@include('landing.modals')        {{-- addModal, leadModal, viewer, toast --}}

<script>window.LANDING = @js($payload)</script>
</body>
</html>
