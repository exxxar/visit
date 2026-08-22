<?php

namespace App\Models;

use App\Concerns\HasModerationLogs;
use App\Enums\ApplicationStatus;
use App\Enums\ModerationAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

    public function approve(): Place
    {
        $email = $this->contact_email ?? $this->email;
        $owner = User::where('email', $email)->first();
        $isNewUser = false;
        $plainPassword = null;

        // 1. Ищем или создаём владельца
        if (!$owner) {
            $plainPassword = Str::password(12, letters: true, numbers: true, symbols: false);

            $owner = User::create([
                'name'     => $this->contact_name ?? $this->org_name,
                'email'    => $email,
                'password' => Hash::make($plainPassword),
            ]);
            $owner->assignRole('business');
            $isNewUser = true;
        } elseif (!$owner->hasRole('business')) {
            $owner->assignRole('business');
        }

        // 2. Создаём заведение из заявки
        $place = Place::create([
            'name'        => $this->org_name,
            'slug'        => Str::slug($this->org_name) . '-' . Str::random(4),
            'category_id' => $this->category_id,
            'district_id' => $this->district_id,
            'address'     => $this->address,
            'phone'       => $this->phone,
            'email'       => $this->email,
            'site'        => $this->site,
            'description' => $this->description,
            'socials'     => $this->socials,
            'owner_id'    => $owner->id,
            'status'      => ModerationAction::Approved,
        ]);

        // 3. Отправляем письмо
        if ($isNewUser) {
            Mail::to($owner->email)->send(
                new PlaceApprovedWithCredentialsMail($place, $owner, $plainPassword)
            );
        } else {
            Mail::to($owner->email)->send(
                new PlaceApprovedMail($place, $owner)
            );
        }

        // 4. Помечаем заявку как одобренную
        $this->update(['status' => 'approved', 'place_id' => $place->id]);

        return $place;
    }
}
