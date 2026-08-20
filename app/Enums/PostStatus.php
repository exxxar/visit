<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

/**
 * Редакционные подборки (журнал).
 */
enum PostStatus: string
{
    use EnumOptions;

    case Draft     = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Черновик',
            self::Published => 'Опубликована',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft     => 'gray',
            self::Published => 'lime',
        };
    }
}
