<?php

namespace App\Models;

use App\Enums\LeadInterest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'company', 'position', 'phone', 'email',
        'interest', 'consent_data', 'consent_policy', 'consent_news', 'utm'];

    protected $casts = [
        'interest'     => LeadInterest::class,
        'consent_data'   => 'boolean',
        'consent_policy' => 'boolean',
        'consent_news'   => 'boolean',
        'utm' => 'array',
    ];
}
