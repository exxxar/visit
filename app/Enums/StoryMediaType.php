<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

enum StoryMediaType: string
{
    use EnumOptions;

    case Photo = 'photo';
    case Video = 'video';

    public function label(): string
    {
        return match ($this) {
            self::Photo => 'Фото',
            self::Video => 'Видео',
        };
    }
}
