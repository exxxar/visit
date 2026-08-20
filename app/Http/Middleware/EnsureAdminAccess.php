<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // нет права view admin — мягко на лендинг вместо 403
        if (! $user || ! $user->can('view admin')) {
            return redirect('/');
        }

        return $next($request);
    }
}
