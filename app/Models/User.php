<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Authenticatable  implements CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, CanResetPasswordTrait;

    protected $fillable = ['name', 'email', 'phone',
        'phone_normalized', 'referred_by', 'meta',
        'password', 'status'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'meta' => 'array',
        'status' => UserStatus::class,
    ];

    public function ownedPlaces(): HasMany
    {
        return $this->hasMany(Place::class, 'owner_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function isFavorite(Place $place): bool
    {
        return $this->favorites()->where('place_id', $place->id)->exists();
    }

    public function toggleFavorite(Place $place): bool
    {
        $favorite = $this->favorites()->where('place_id', $place->id)->first();

        if ($favorite) {
            $favorite->delete();
            return false; // Удалено из избранного
        } else {
            $this->favorites()->create(['place_id' => $place->id]);
            return true; // Добавлено в избранное
        }
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function isBlocked(): bool
    {
        return $this->status === UserStatus::Blocked;
    }



    public function sendPasswordResetNotification($token): void
    {
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ], false));


        $this->notify(new ResetPasswordNotification($url));
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(ReferralInvite::class, 'referrer_id');
    }

    public function partnerType(): ?string
    {
        if ($this->hasRole('admin'))        return 'admin';
        if ($this->hasRole('tech_partner')) return 'tech';
        if ($this->hasRole('ad_partner'))   return 'ad';

        return $this->meta['partner_type'] ?? null;
    }

    // Связи
    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(PartnerReview::class, 'client_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(PartnerReview::class, 'partner_id');
    }

// Генерация slug при создании
    protected static function booted()
    {
        static::creating(function ($user) {
            if (!$user->slug) {
                $user->slug = self::generateUniqueSlug($user->name);
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

// Проверка, может ли пользователь оставить отзыв
    public function canReviewFor($request): bool
    {
        // Определяем поле партнёра в зависимости от типа заявки
        $partnerField = $request instanceof \App\Models\PromotionRequest
            ? 'partner_id'
            : 'tech_partner_id';

        return $request->user_id === $this->id
            && $request->status === 'completed'
            && $request->{$partnerField} !== null
            && !PartnerReview::where('client_id', $this->id)
                ->where('reviewable_type', get_class($request))
                ->where('reviewable_id', $request->id)
                ->exists();
    }
}
