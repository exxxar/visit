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
