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

        // заблокированным вход закрыт
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
            $user->hasAnyRole(['admin', 'moderator', 'editor']) => '/admin',
            $user->hasRole('business')                          => '/account',
            default                                             => '/',
        };
    }
}
