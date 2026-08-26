<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = "feedbacks";

    protected $fillable = [
        'name', 'contact', 'subject', 'message',
        'status', 'ip', 'user_agent', 'admin_note',
    ];

    public const STATUS_NEW        = 'new';
    public const STATUS_PROGRESS   = 'in_progress';
    public const STATUS_RESOLVED   = 'resolved';

    public const SUBJECTS = [
        'general'      => 'Общее',
        'partnership'  => 'Сотрудничество',
        'ads'          => 'Реклама',
        'bug'          => 'Ошибка на сайте',
        'suggestion'   => 'Предложение',
        'complaint'    => 'Жалоба',
    ];

    public function scopeNew($q)      { return $q->where('status', self::STATUS_NEW); }
    public function scopeUnread($q)   { return $q->whereIn('status', [self::STATUS_NEW, self::STATUS_PROGRESS]); }

    public function subjectLabel(): string
    {
        return self::SUBJECTS[$this->subject] ?? $this->subject;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_NEW      => 'Новое',
            self::STATUS_PROGRESS => 'В работе',
            self::STATUS_RESOLVED => 'Закрыто',
            default               => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_NEW      => 'cyan',
            self::STATUS_PROGRESS => 'yellow',
            self::STATUS_RESOLVED => 'lime',
            default               => 'gray',
        };
    }
}
