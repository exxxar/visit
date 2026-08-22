<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

enum StoryStatus: string
{
    use EnumOptions;

    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'На модерации',
            self::Approved => 'Опубликована',
            self::Rejected => 'Отклонена',
            self::Archived => 'В архиве',
        };
    }
}
