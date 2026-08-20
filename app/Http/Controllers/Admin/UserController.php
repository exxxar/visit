<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::query()
                ->with('roles')
                ->when($request->q, fn ($q, $s) => $q->where('name', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%"))
                ->latest()->paginate(15)->withQueryString(),
            'allRoles' => Role::pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('manage users'), 403);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles'    => ['array'],
        ]);

        $user = User::create($data);
        $user->syncRoles($data['roles'] ?? []);

        return back()->with('success', 'Пользователь создан');
    }

    public function update(Request $request, User $user)
    {
        abort_unless($request->user()->can('manage users'), 403);

        $data = $request->validate([
            'status' => ['nullable', Rule::enum(UserStatus::class)],
            'roles'  => ['array'],
        ]);

        if (isset($data['status'])) $user->update(['status' => $data['status']]);

        if ($request->user()->can('assign roles') && isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return back()->with('success', 'Пользователь обновлён');
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->can('manage users'), 403);
        abort_if($user->id === $request->user()->id, 422, 'Нельзя удалить себя');

        $user->delete();

        return back()->with('success', 'Пользователь удалён');
    }
}
