<?php

namespace App\Models;

use App\Concerns\HasModerationLogs;
use App\Enums\ApplicationStatus;
use App\Enums\ModerationAction;
use App\Mail\PlaceApprovedMail;
use App\Mail\PlaceApprovedWithCredentialsMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Application extends Model
{
    use HasFactory, HasModerationLogs;

    protected $fillable = [   'org_name',  'category', 'district',
        'address', 'phone', 'email', 'site', 'description', 'socials',
        'contact_name', 'contact_position', 'contact_phone', 'contact_email',
        'media', 'lat', 'lng', 'working_hours',
        'external_id', 'external_source',
        'status', 'place_id',
        ];

    protected $casts = [
        'status' => ApplicationStatus::class,
        'media'  => 'array',
        'socials' => 'array',
        'working_hours' => 'array',
        'lat'           => 'float',
        'lng'           => 'float',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function approve(): Place
    {
        $email = $this->contact_email ?? $this->email;
        $owner = User::where('email', $email)->first();
        $isNewUser = false;
        $plainPassword = null;

        /* 1. Владелец */
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

        /* 2. Резолв категории (slug/name → id) */
        $categoryId = $this->resolveCategoryId();
        if (!$categoryId) {
            $categoryId = Category::whereNull('parent_id')->orderBy('sort')->value('id');
        }

        /* 3. Резолв района (name → id) */
        $districtId = $this->resolveDistrictId();
        if (!$districtId) {
            $districtId = District::orderBy('sort')->value('id');
        }

        /* 4. Создание заведения */
        $place = Place::create([
            'name'              => $this->org_name,
            'slug'              => $this->makeUniqueSlug(),
            'category_id'       => $categoryId,
            'district_id'       => $districtId,
            'address'           => $this->address,
            'phone'             => $this->phone,
            'email'             => $this->email,
            'site'              => $this->site,
            'description'       => $this->description,
            'socials'           => $this->socials,
            'lat'               => $this->lat,
            'lng'               => $this->lng,
            'working_hours'     => $this->working_hours,
            'external_id'       => $this->external_id,
            'external_source'   => $this->external_source,
            'owner_id'          => $owner->id,
            'status'            => ModerationAction::Approved,
        ]);

        /* 5. Письмо */
        if ($isNewUser) {
            Mail::to($owner->email)->send(
                new PlaceApprovedWithCredentialsMail($place, $owner, $plainPassword)
            );
        } else {
            Mail::to($owner->email)->send(
                new PlaceApprovedMail($place, $owner)
            );
        }

        $this->update(['status' => 'approved']);

        return $place;
    }

    /* ---------- хелперы ---------- */

    protected function resolveCategoryId(): ?int
    {
        $raw = $this->category_id ?? $this->category ?? null;
        if (!$raw) return null;

        // если уже int — это id
        if (is_int($raw) || ctype_digit((string) $raw)) {
            return Category::where('id', $raw)->exists() ? (int) $raw : null;
        }

        // ищем по slug, затем по name
        return Category::where('slug', $raw)->value('id')
            ?? Category::where('name', $raw)->value('id');
    }

    protected function resolveDistrictId(): ?int
    {
        $raw = $this->district_id ?? $this->district ?? null;
        if (!$raw) return null;

        if (is_int($raw) || ctype_digit((string) $raw)) {
            return District::where('id', $raw)->exists() ? (int) $raw : null;
        }

        return District::where('slug', Str::slug($raw))->value('id')
            ?? District::where('name', $raw)->value('id');
    }

    protected function makeUniqueSlug(): string
    {
        $base = Str::slug($this->org_name);
        $slug = $base;
        $i = 1;
        while (Place::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
