<?php

namespace App\Http\Controllers;

use App\Enums\ModerationStatus;
use App\Helpers\PhoneNormalizer;
use App\Models\Application;
use App\Models\District;
use App\Models\Place;
use App\Models\ReferralInvite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ReferralController extends Controller
{

    /**
     * Форма продвижения заведения
     */
    public function promoteForm()
    {
        $phone = session('referral_phone');
        if (!$phone) return redirect('/');

        return view('referral.promote', [
            'phone'          => $phone,
            'phoneFormatted' => PhoneNormalizer::format($phone),
            'services'       => \App\Models\PromotionRequest::SERVICES,
        ]);
    }
    public function registerPromote(Request $request)
    {
        $data = $request->validate([
            // данные заведения
            'place_name'    => ['required', 'string', 'max:200'],
            'place_address' => ['nullable', 'string', 'max:255'],
            'place_phone'   => ['nullable', 'string', 'max:30'],
            'place_site'    => ['nullable', 'string', 'max:255'],

            // контактное лицо
            'contact_name'  => ['required', 'string', 'max:100'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:100'],

            // массив выбранных услуг
            'services'      => ['required', 'array', 'min:1'],
            'services.*'    => ['in:smm,targeting,content,video,influencer,complex'],
            'requirements'  => ['nullable', 'string', 'max:1000'],

            // регистрация (опционально)
            'email'         => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['nullable', 'string', 'min:8', 'required_with:email'],
        ], [
            'place_name.required' => 'Укажите название заведения',
            'contact_name.required' => 'Укажите имя контактного лица',
            'contact_phone.required' => 'Укажите телефон для связи',
            'services.required' => 'Выберите хотя бы одну услугу',
            'services.min' => 'Выберите хотя бы одну услугу',
        ]);

        $refPhone = session('referral_phone');
        $referrer =  User::where('phone', $request->phone)->first();

        // Определяем, является ли реферер рекламным партнёром
        $partnerId = null;
        if ($referrer) {
            $isAdPartner = $referrer->hasRole('ad_partner')
                || ($referrer->meta['partner_type'] ?? null) === 'ad';

            if ($isAdPartner) {
                $partnerId = $referrer->id;
            }
        }

        // Создаём пользователя, если указаны email и пароль
        $user = null;
        if (!empty($data['email']) && !empty($data['password'])) {
            $user = User::create([
                'name'             => $data['contact_name'],
                'email'            => $data['email'],
                'password'         => Hash::make($data['password']),
                'phone'            => $data['contact_phone'],
               // 'phone_normalized' => PhoneNormalizer::normalize($data['contact_phone']),
                'referred_by'      => $referrer?->id,
            ]);

            ReferralController::bindPendingInvites($user);
        }

        // Создаём заявки для каждой выбранной услуги
        $services = \App\Models\PromotionRequest::SERVICES;
        $createdRequests = [];
        $totalAmount = 0;

        foreach ($data['services'] as $serviceId) {
            $svc = $services[$serviceId] ?? null;
            if (!$svc) continue;

            $promotion = \App\Models\PromotionRequest::create([
                'user_id'      => $user?->id,
                'place_id'     => null,
                'service'      => $serviceId,
                'title'        => $svc['label'],
                'amount'       => $svc['price'],
                'status'       => 'new',
                'partner_id'   => $partnerId,
                'requirements' => $data['requirements'] ?? null,
                'meta'         => [
                    'place_name'    => $data['place_name'],
                    'place_address' => $data['place_address'] ?? null,
                    'place_phone'   => $data['place_phone'] ?? null,
                    'place_site'    => $data['place_site'] ?? null,
                    'contact_name'  => $data['contact_name'],
                    'contact_phone' => $data['contact_phone'],
                    'contact_email' => $data['contact_email'] ?? null,
                    'referrer_phone'=> $refPhone,
                    'created_via'   => 'referral_promote',
                ],
            ]);

            $createdRequests[] = $promotion;
            $totalAmount += $svc['price'];
        }

        // Создаём запись в рефералах
        if ($user) {
            \App\Models\ReferralInvite::create([
                'referrer_phone'   => $refPhone,
                'referrer_id'      => $referrer?->id,
                'referred_user_id' => $user->id,
                'role'             => \App\Models\ReferralInvite::ROLE_CLIENT,
                'place_id'         => null,
                'status'           => $referrer
                    ? \App\Models\ReferralInvite::STATUS_ACTIVE
                    : \App\Models\ReferralInvite::STATUS_PENDING,
                'meta'             => [
                    'ip'              => $request->ip(),
                    'user_agent'      => substr($request->userAgent() ?? '', 0, 300),
                    'registered_at'   => now()->toIso8601String(),
                    'promotion_ids'   => array_map(fn($r) => $r->id, $createdRequests),
                ],
            ]);
        }

        // Отправляем email-уведомление
        if (!empty($data['contact_email'])) {
            try {
                \App\Mail\PromotionRequestCreated::dispatch(
                    $data['contact_email'],
                    $data['contact_name'],
                    $data['place_name'],
                    $createdRequests,
                    $totalAmount
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send promotion email: ' . $e->getMessage());
            }
        }

        // Если пользователь создан — логиним
        if ($user) {
            Auth::login($user);
            return redirect('/account')
                ->with('success', "Заявки на продвижение созданы (" . count($createdRequests) . " шт.). Рекламный партнёр скоро свяжется с вами.");
        }

        return redirect('/')
            ->with('success', "Заявки на продвижение созданы (" . count($createdRequests) . " шт.). С вами скоро свяжутся.");
    }

    public function index(Request $request)
    {
        $query = ReferralInvite::query()
            ->with(['referrer:id,name,email,phone', 'referred:id,name,email,phone', 'place:id,name,slug']);

        $query->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
            $w->where('referrer_phone', 'like', "%$s%")
                ->orWhereHas('referrer', fn ($u) => $u->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
                ->orWhereHas('referred', fn ($u) => $u->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
                ->orWhereHas('place', fn ($p) => $p->where('name', 'like', "%$s%"));
        }));

        $query->when($request->role, fn ($q, $r) => $q->where('role', $r));
        $query->when($request->status, fn ($q, $s) => $q->where('status', $s));

        $invites = $query->latest()->paginate(25)->withQueryString();

        return Inertia::render('Admin/Referrals/Index', [
            'invites' => $invites,
            'counts'  => [
                'total'       => ReferralInvite::count(),
                'pending'     => ReferralInvite::where('status', ReferralInvite::STATUS_PENDING)->count(),
                'active'      => ReferralInvite::where('status', ReferralInvite::STATUS_ACTIVE)->count(),
                'completed'   => ReferralInvite::where('status', ReferralInvite::STATUS_COMPLETED)->count(),
                'clients'     => ReferralInvite::where('role', ReferralInvite::ROLE_CLIENT)
                    ->whereNotNull('referred_user_id')->count(),
                'tech'        => ReferralInvite::where('role', ReferralInvite::ROLE_TECH)
                    ->whereNotNull('referred_user_id')->count(),
                'ad'          => ReferralInvite::where('role', ReferralInvite::ROLE_AD)
                    ->whereNotNull('referred_user_id')->count(),
                'week'        => ReferralInvite::where('created_at', '>=', now()->subDays(7))->count(),
            ],
        ]);
    }
    /**
     * Страница выбора действия
     */
    public function choose(Request $request)
    {
        $phone = $request->query('phone') ?? session('referral_phone');
        $normalized = PhoneNormalizer::normalize($phone);

        if (!$normalized) {
            return redirect('/');
        }

        // ищем реферера (если он уже зарегистрирован)
        $referrer = User::where('phone', $normalized)->first();

        // СОЗДАЁМ pending-приглашение при первом переходе
        // защита от дублей: одна запись на сессию/IP в течение 24 часов
        $sessionId = session()->getId();
        $existingInvite = ReferralInvite::where('referrer_phone', $normalized)
            ->whereNull('referred_user_id')
            ->where('status', ReferralInvite::STATUS_PENDING)
            ->where('created_at', '>=', now()->subDay())
            ->whereJsonContains('meta->session_id', $sessionId)
            ->first();

        if (!$existingInvite) {
            ReferralInvite::create([
                'referrer_phone'   => $normalized,
                'referrer_id'      => $referrer?->id,
                'referred_user_id' => null,
                'role'             => ReferralInvite::ROLE_CLIENT, // по умолчанию, изменится при регистрации
                'status'           => ReferralInvite::STATUS_PENDING,
                'meta'             => [
                    'session_id' => $sessionId,
                    'ip'         => $request->ip(),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 300),
                    'visited_at' => now()->toIso8601String(),
                    'source'     => 'direct_link',
                ],
            ]);
        }

        return view('referral.choose', [
            'phone'          => $normalized,
            'phoneFormatted' => PhoneNormalizer::format($normalized),
            'referrer'       => $referrer,
        ]);
    }

    /**
     * Форма: клиент (создание заведения)
     */
    public function clientForm()
    {
        $phone = session('referral_phone');
        if (!$phone) return redirect('/');

        return view('referral.client', [
            'phone'          => $phone,
            'phoneFormatted' => PhoneNormalizer::format($phone),
            'districts'      => District::orderBy('sort')->get(['id', 'name']),
        ]);
    }

    /**
     * Форма: технический партнёр
     */
    public function techForm()
    {
        $phone = session('referral_phone');
        if (!$phone) return redirect('/');

        return view('referral.tech', [
            'phone'          => $phone,
            'phoneFormatted' => PhoneNormalizer::format($phone),
        ]);
    }

    /**
     * Форма: рекламный партнёр
     */
    public function adForm()
    {
        $phone = session('referral_phone');
        if (!$phone) return redirect('/');

        return view('referral.ad', [
            'phone'          => $phone,
            'phoneFormatted' => PhoneNormalizer::format($phone),
        ]);
    }

    public function registerClient(Request $request)
    {
        $data = $request->validate([
            // заведение
            'place_name'       => ['required', 'string', 'max:200'],
            'category'         => ['required', 'string', 'max:100'],
            'district_id'      => ['nullable', 'exists:districts,id'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'address'          => ['nullable', 'string', 'max:255'],
            'place_phone'      => ['nullable', 'string', 'max:30'],
            'place_email'      => ['nullable', 'email', 'max:100'],
            'site'             => ['nullable', 'string', 'max:255'],

            // контактное лицо
            'contact_name'     => ['required', 'string', 'max:100'],
            'contact_position' => ['nullable', 'string', 'max:100'],
            'contact_phone'    => ['nullable', 'string', 'max:30'],
            'contact_email'    => ['nullable', 'email', 'max:100'],

            // регистрация
            'email'            => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // медиа
            'media'            => ['nullable', 'array', 'max:20'],
            'media.*'          => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'social_handle'    => ['nullable', 'string', 'max:100'],
            'social_site'      => ['nullable', 'url', 'max:255'],

            'extra_services' => ['nullable', 'string'], // JSON массив ID услуг
        ], [
            'place_name.required' => 'Укажите название заведения',
            'category.required'   => 'Выберите категорию',
            'contact_name.required' => 'Укажите имя контактного лица',
            'email.required'      => 'Укажите email для входа',
            'email.unique'        => 'Пользователь с таким email уже зарегистрирован',
            'password.required'   => 'Укажите пароль',
            'password.min'        => 'Пароль должен быть минимум 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ]);

        $refPhone = session('referral_phone');
        $referrer = User::where('phone', $refPhone)->first();
        $sessionId = session()->getId();

        // 1. Создаём пользователя
        $user = User::create([
            'name'             => $data['contact_name'],
            'email'            => $data['email'],
            'password'         => Hash::make($data['password']),
            'phone'            => $data['contact_phone'] ?? $data['place_phone'] ?? null,
           /* 'phone_normalized' => \App\Helpers\PhoneNormalizer::normalize(
                $data['contact_phone'] ?? $data['place_phone'] ?? null
            ),*/
            'referred_by'      => $referrer?->id,
        ]);

        // привязываем pending-приглашения
        self::bindPendingInvites($user);

        // 2. Сохраняем загруженные медиа
        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $mediaPaths[] = $file->store('places/media', 'public');
            }
        }

        // 3. Собираем соцсети
        $socials = [];
        if (!empty($data['social_handle'])) {
            $handle = ltrim($data['social_handle'], '@');
            $socials['handle'] = $handle;
            if (str_contains($handle, 't.me') || str_contains($handle, 'telegram')) {
                $socials['telegram'] = $handle;
            } elseif (str_contains($handle, 'vk.com')) {
                $socials['vk'] = $handle;
            } elseif (str_contains($handle, 'instagram') || str_contains($handle, '@')) {
                $socials['instagram'] = $handle;
            }
        }
        if (!empty($data['social_site'])) {
            $socials['site'] = $data['social_site'];
        }

        // 4. Создаём заведение
        $slug = \Illuminate\Support\Str::slug($data['place_name']);
        while (Place::where('slug', $slug)->exists()) {
            $slug .= '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(3));
        }

        $place = Place::create([
            'name'              => $data['place_name'],
            'slug'              => $slug,
            'address'           => $data['address'] ?? null,
            'district_id'       => $data['district_id'] ?? null,
            'short_description' => $data['description'] ?? null,
            'phone'             => $data['place_phone'] ?? null,
            'email'             => $data['place_email'] ?? null,
            'site'              => $data['site'] ?? null,
            'socials'           => $socials ?: null,
            'owner_id'          => $user->id,
            'referred_by'       => $referrer?->id,
            'status'            => \App\Enums\ModerationStatus::OnModeration,
        ]);

        // 5. Сохраняем фото как PlacePhoto
        foreach ($mediaPaths as $i => $path) {
            $place->photos()->create([
                'path'     => '/storage/' . $path,
                'sort'     => $i,
                'is_cover' => $i === 0,
            ]);
        }

        $extras = [];
        if (!empty($data['extra_services'])) {
            $decoded = json_decode($data['extra_services'], true);
            if (is_array($decoded)) {
                $allowed = ['app_food', 'app_beauty', 'app_fitness', 'app_auto', 'app_blog', 'landing'];
                $extras = array_intersect($decoded, $allowed);
            }
        }

        // 6. Создаём заявку (для отслеживания в модерации)
        Application::create([
            'org_name'        => $data['place_name'],
            'category'        => $data['category'],
            'district'        => $data['district_id']
                ? \App\Models\District::find($data['district_id'])?->name
                : null,
            'address'         => $data['address'] ?? null,
            'phone'           => $data['place_phone'] ?? null,
            'email'           => $data['place_email'] ?? null,
            'site'            => $data['site'] ?? null,
            'description'     => $data['description'] ?? null,
            'contact_name'    => $data['contact_name'],
            'contact_position'=> $data['contact_position'] ?? null,
            'contact_phone'   => $data['contact_phone'] ?? null,
            'contact_email'   => $data['contact_email'] ?? null,
            'meta' => [
                'extra_services' => $extras,
            ],
            'media'           => $mediaPaths,
            'socials'         => $socials ?: null,
            'status'          => \App\Enums\ApplicationStatus::New,
            'place_id'        => $place->id,
            'external_source' => 'referral',
            'external_id'     => $refPhone,
        ]);


        // 8. Создаём заявки на разработку для выбранных доп. услуг
        if (!empty($extras)) {
            foreach ($extras as $serviceId) {
                if (!isset(\App\Models\DevelopmentRequest::SERVICES[$serviceId])) {
                    continue;
                }

                $svc = \App\Models\DevelopmentRequest::SERVICES[$serviceId];

                \App\Models\DevelopmentRequest::create([
                    'user_id'      => $user->id,
                    'place_id'     => $place->id,
                    'service'      => $serviceId,
                    'title'        => $svc['label'],
                    'amount'       => $svc['price'],
                    'status'       => \App\Models\DevelopmentRequest::STATUS_NEW,
                    'requirements' => "Заведение: {$place->name}\nКатегория: {$data['category']}\nАдрес: " . ($data['address'] ?? 'не указан'),
                    'meta'         => [
                        'category'    => $data['category'],
                        'district_id' => $data['district_id'] ?? null,
                        'created_via' => 'referral_form',
                    ],
                ]);
            }
        }

        // 7. ИЩЕМ СУЩЕСТВУЮЩЕЕ приглашение
        $invite = ReferralInvite::where('referrer_phone', $refPhone)
            ->whereNull('referred_user_id')
            ->where('status', ReferralInvite::STATUS_PENDING)
            ->whereJsonContains('meta->session_id', $sessionId)
            ->first();

        if ($invite) {
            // обновляем существующую запись
            $invite->update([
                'referred_user_id' => $user->id,
                'role'             => ReferralInvite::ROLE_CLIENT,
                'place_id'         => $place->id,
                'status'           => $referrer
                    ? ReferralInvite::STATUS_ACTIVE
                    : ReferralInvite::STATUS_PENDING,
                'meta'             => array_merge($invite->meta ?? [], [
                    'registered_at' => now()->toIso8601String(),
                    'category'      => $data['category'],
                    'media_count'   => count($mediaPaths),
                ]),
            ]);
        } else {
            // создаём новую, если по какой-то причине не нашли
            ReferralInvite::create([
                'referrer_phone'   => $refPhone,
                'referrer_id'      => $referrer?->id,
                'referred_user_id' => $user->id,
                'role'             => ReferralInvite::ROLE_CLIENT,
                'place_id'         => $place->id,
                'status'           => $referrer
                    ? ReferralInvite::STATUS_ACTIVE
                    : ReferralInvite::STATUS_PENDING,
                'meta'             => [
                    'session_id'    => $sessionId,
                    'ip'            => $request->ip(),
                    'user_agent'    => substr($request->userAgent() ?? '', 0, 300),
                    'registered_at' => now()->toIso8601String(),
                    'category'      => $data['category'],
                    'media_count'   => count($mediaPaths),
                ],
            ]);
        }


        Auth::login($user);

        $msg = "Заведение «{$place->name}» создано и отправлено на модерацию.";
        if (count($extras)) {
            $msg .= " Выбранные доп. услуги можно оплатить в личном кабинете.";
        }

        return redirect('/account')->with('success', $msg);
    }

    /**
     * Регистрация технического партнёра
     */
    public function registerTech(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:8'],
            'user_phone'  => ['required', 'string', 'max:20'],
            'speciality'  => ['required', 'string', 'max:200'],
            'experience'  => ['nullable', 'string', 'max:500'],
            'portfolio'   => ['nullable', 'url', 'max:500'],
        ]);

        $refPhone = session('referral_phone');
        $referrer = User::where('phone', $refPhone)->first();

        $user = User::create([
            'name'             => $data['name'],
            'email'            => $data['email'],
            'password'         => Hash::make($data['password']),
            'phone'            => $data['user_phone'],
          /*  'phone_normalized' => PhoneNormalizer::normalize($data['user_phone']),*/
            'referred_by'      => $referrer?->id,
            'meta'             => [
                'speciality' => $data['speciality'],
                'experience' => $data['experience'] ?? null,
                'portfolio'  => $data['portfolio'] ?? null,
                'partner_type' => 'tech',
            ],
        ]);

        // назначаем роль, если есть Spatie
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('tech_partner');
        }

        ReferralInvite::create([
            'referrer_phone'   => $refPhone,
            'referrer_id'      => $referrer?->id,
            'referred_user_id' => $user->id,
            'role'             => ReferralInvite::ROLE_TECH,
            'status'           => $referrer ? ReferralInvite::STATUS_ACTIVE : ReferralInvite::STATUS_PENDING,
            'meta'             => [
                'speciality' => $data['speciality'],
                'ip'         => $request->ip(),
                'registered_at' => now()->toIso8601String(),
            ],
        ]);

        Auth::login($user);

        return redirect('/account')
            ->with('success', 'Добро пожаловать! Вы зарегистрированы как технический партнёр.');
    }

    /**
     * Регистрация рекламного партнёра
     */
    public function registerAd(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:8'],
            'user_phone'   => ['required', 'string', 'max:20'],
            'platform'     => ['required', 'string', 'max:100'],
            'audience'     => ['nullable', 'string', 'max:100'],
            'social_link'  => ['nullable', 'url', 'max:500'],
        ]);

        $refPhone = session('referral_phone');
        $referrer = User::where('phone', $refPhone)->first();

        $user = User::create([
            'name'             => $data['name'],
            'email'            => $data['email'],
            'password'         => Hash::make($data['password']),
            'phone'            => $data['user_phone'],
          /*  'phone_normalized' => PhoneNormalizer::normalize($data['user_phone']),*/
            'referred_by'      => $referrer?->id,
            'meta'             => [
                'platform'     => $data['platform'],
                'audience'     => $data['audience'] ?? null,
                'social_link'  => $data['social_link'] ?? null,
                'partner_type' => 'ad',
            ],
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('ad_partner');
        }

        ReferralInvite::create([
            'referrer_phone'   => $refPhone,
            'referrer_id'      => $referrer?->id,
            'referred_user_id' => $user->id,
            'role'             => ReferralInvite::ROLE_AD,
            'status'           => $referrer ? ReferralInvite::STATUS_ACTIVE : ReferralInvite::STATUS_PENDING,
            'meta'             => [
                'platform' => $data['platform'],
                'ip'       => $request->ip(),
                'registered_at' => now()->toIso8601String(),
            ],
        ]);

        Auth::login($user);

        return redirect('/account')
            ->with('success', 'Добро пожаловать! Вы зарегистрированы как рекламный партнёр.');
    }

    /**
     * Хук регистрации: привязывает pending-приглашения к новому пользователю
     * Вызывать из обычного RegisterController/Auth после создания юзера
     */
    public static function bindPendingInvites(User $user): void
    {
        $phone = $user->phone;
        if (!$phone) return;

        $pending = ReferralInvite::where('referrer_phone', $phone)
            ->whereNull('referrer_id')
            ->get();

        if ($pending->isEmpty()) return;

        foreach ($pending as $invite) {
            $invite->update([
                'referrer_id' => $user->id,
                'status'      => ReferralInvite::STATUS_ACTIVE,
            ]);

            // если у реферала ещё нет referred_by — привязываем
            if ($invite->referred_user_id && !$invite->referred?->referred_by) {
                $invite->referred?->update(['referred_by' => $user->id]);
            }
            if ($invite->place_id && !$invite->place?->referred_by) {
                $invite->place?->update(['referred_by' => $user->id]);
            }
        }
    }
}
