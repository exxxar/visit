<?php

namespace App\Helpers;

class PhoneNormalizer
{
    /**
     * Приводит любой номер к формату 79494320661 (11 цифр, начинается с 7).
     *
     * Примеры:
     *   +79494320661 → 79494320661
     *   89494320661  → 79494320661
     *   8 (949) 432-06-61 → 79494320661
     *   9494320661   → 79494320661
     */
    public static function normalize(?string $phone): ?string
    {
        if (!$phone) return null;

        // убираем всё кроме цифр
        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 10) {
            // 9494320661 → 79494320661
            return '7' . $digits;
        }

        if (strlen($digits) === 11) {
            // 89494320661 → 79494320661
            return ($digits[0] === '8' ? '7' : $digits[0]) . substr($digits, 1);
        }

        return null;
    }

    /**
     * Форматирует номер для отображения: +7 (949) 432-06-61
     */
    public static function format(?string $phone): ?string
    {
        $n = self::normalize($phone);
        if (!$n || strlen($n) !== 11) return $phone;

        return sprintf(
            '+%s (%s) %s-%s-%s',
            $n[0],
            substr($n, 1, 3),
            substr($n, 4, 3),
            substr($n, 7, 2),
            substr($n, 9, 2)
        );
    }
}
