<?php

namespace App\Enums;

use App\Concerns\EnumOptions;

/**
 * Заявки «Добавить предприятие» из 4-шаговой формы.
 */
enum ApplicationStatus: string
{
    use EnumOptions;

    case New      = 'new';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New      => 'Новая',
            self::InReview => 'В работе',
            self::Approved => 'Одобрена',
            self::Rejected => 'Отклонена',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New      => 'cyan',
            self::InReview => 'yellow',
            self::Approved => 'lime',
            self::Rejected => 'rose',
        };
    }
}
