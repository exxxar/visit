<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminAuditLog extends Model
{
    protected $fillable = [
        'admin_id', 'action', 'auditable_type', 'auditable_id',
        'old_values', 'new_values', 'ip', 'user_agent', 'comment',
    ];
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
    public function auditable(): MorphTo { return $this->morphTo(); }

    public static function log(string $action, Model $target, ?array $old = null, ?array $new = null, ?string $comment = null): void
    {
        $admin = auth()->user();
        if (!$admin) return;

        self::create([
            'admin_id'       => $admin->id,
            'action'         => $action,
            'auditable_type' => get_class($target),
            'auditable_id'   => $target->id,
            'old_values'     => $old,
            'new_values'     => $new,
            'ip'             => request()->ip(),
            'user_agent'     => substr(request()->userAgent() ?? '', 0, 300),
            'comment'        => $comment,
        ]);
    }
}
