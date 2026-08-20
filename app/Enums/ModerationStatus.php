<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

/**
 * Общий статус модерации для places, events, news.
 */
enum ModerationStatus: string
{
    use EnumOptions;

    case Draft        = 'draft';
    case OnModeration = 'on_moderation';
    case Approved     = 'approved';
    case Rejected     = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft        => 'Черновик',
            self::OnModeration => 'На модерации',
            self::Approved     => 'Опубликовано',
            self::Rejected     => 'Отклонено',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft        => 'gray',
            self::OnModeration => 'yellow',
            self::Approved     => 'lime',
            self::Rejected     => 'rose',
        };
    }

    /** Видно ли на публичном сайте */
    public function isPublic(): bool
    {
        return $this === self::Approved;
    }
}
