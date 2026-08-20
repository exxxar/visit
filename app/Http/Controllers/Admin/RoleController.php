<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Roles/Index', [
            'roles'       => Role::with('permissions')->get(),
            'allPermissions' => Permission::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        abort_unless($request->user()->can('assign roles'), 403);

        $data = $request->validate(['permissions' => ['array']]);

        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', "Права роли «{$role->name}» обновлены");
    }
}
