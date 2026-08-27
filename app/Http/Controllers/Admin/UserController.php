<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with('roles')
            ->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%");
            }))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users'    => $users,
            'allRoles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles'    => ['array'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return back()->with('success', 'Пользователь создан');
    }

    /* профиль + роли + статус */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'roles'  => ['array'],
            'status' => ['required', 'in:active,blocked'],
        ]);

        $user->update([
            'name'   => $data['name'],
            'email'  => $data['email'],
            'status' => $data['status'],
        ]);
        $user->syncRoles($data['roles'] ?? []);

        return back()->with('success', 'Пользователь обновлён');
    }

    /* смена пароля админом */
    public function updatePassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Пароль изменён');
    }

    /* отправка ссылки сброса на почту пользователя */
    public function sendResetLink(User $user)
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', "Ссылка для сброса пароля отправлена на {$user->email}")
            : back()->with('error', 'Не удалось отправить ссылку сброса');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Нельзя удалить собственный аккаунт');
        }
        $user->delete();

        return back()->with('success', 'Пользователь удалён');
    }
}
