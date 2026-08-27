<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('safe_img')) {
    function safe_img(?string $path, string $default = '/assets/placeholder.jpg'): string
    {
        if (!$path) return $default;

        $relative = str_replace('/storage/', '', $path);

        return Storage::disk('public')->exists($relative)
            ? '/storage/' . $relative
            : $default;
    }
}


if (!function_exists('ruPlural')) {
    /**
     * Русское склонение по числу.
     * Пример: ruPlural(248, ['место', 'места', 'мест']) → 'мест'
     */
    function ruPlural(int $n, array $forms): string
    {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) return $forms[2];
        if ($n1 > 1 && $n1 < 5) return $forms[1];
        if ($n1 === 1) return $forms[0];
        return $forms[2];
    }
}
