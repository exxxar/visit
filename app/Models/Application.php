<?php

namespace App\Models;

use App\Concerns\HasModerationLogs;
use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory, HasModerationLogs;

    protected $fillable = ['user_id', 'org_name', 'category', 'district', 'address',
        'phone', 'email', 'site', 'description', 'contact_name', 'contact_position',
        'contact_phone', 'contact_email', 'media', 'socials', 'status'];

    protected $casts = [
        'status' => ApplicationStatus::class,
        'media'  => 'array',
        'socials' => 'array',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
