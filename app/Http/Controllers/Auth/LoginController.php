<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        // Заблокированным вход закрыт
        if (Auth::user()->isBlocked()) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'Аккаунт заблокирован администратором']);
        }

        $request->session()->regenerate();

        $user = User::query()->findOrFail(Auth::user()->id);

        return redirect()->intended($this->redirectFor($user));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /** Куда после входа — по роли */
    protected function redirectFor(User $user): string
    {
        return match (true) {
            // 1. Админы и модераторы идут в админ-панель
            $user->hasAnyRole(['admin', 'moderator', 'editor']) => '/admin',

            // 2. Бизнес и партнеры идут в бизнес-кабинет
            $user->hasAnyRole(['business', 'tech_partner', 'ad_partner']) => '/account',

            // 3. Туристы (роль user) идут в свой кабинет туриста
            $user->hasRole('user') => '/account-users',

            // 4. Если роли нет вообще (например, новый пользователь), тоже отправляем в /account
            // (Там сработает умная логика из routes/web.php, которая покажет TouristDashboard)
            default => '/account-users',
        };
    }
}
