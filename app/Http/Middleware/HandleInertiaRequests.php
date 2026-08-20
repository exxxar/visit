<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'csrfToken' => csrf_token(),
            'auth.user' => $request->user() ? [
                ...$request->user()->only(['id', 'name', 'email']),
                'roles'       => $request->user()->getRoleNames()->values()->toArray(),
                'permissions' => $request->user()->getAllPermissions()->pluck('name')->values()->toArray(),
            ] : null,

            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
        ];
    }
}
