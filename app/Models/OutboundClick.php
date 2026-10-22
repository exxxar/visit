<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundClick extends Model
{
    protected $fillable = [
        'url', 'domain', 'context', 'place_id',
        'user_id', 'ip', 'user_agent', 'referer', 'page_url',
    ];

    public function place(): BelongsTo { return $this->belongsTo(Place::class); }
    public function user(): BelongsTo  { return $this->belongsTo(User::class); }

    /**
     * Извлечение домена из URL
     */
    public static function extractDomain(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        return $host ? strtolower(preg_replace('/^www\./', '', $host)) : 'unknown';
    }

    /**
     * Определение контекста по URL и странице-источнику
     */
    public static function guessContext(string $pageUrl): ?string
    {
        return match (true) {
            str_contains($pageUrl, '/place/')    => 'place',
            str_contains($pageUrl, '/news/')     => 'news',
            str_contains($pageUrl, '/event/')    => 'event',
            str_contains($pageUrl, '/story')     => 'story',
            str_contains($pageUrl, '/category')  => 'category',
            str_contains($pageUrl, '/ref/')      => 'referral',
            default                              => null,
        };
    }
}
