<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAuthorized
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $userId = $request->route('user'); // Zorg dat de parameter overeenkomt met de route parameter naam

        if ($user->id == $userId || $user->hasRole('admin')) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'Unauthorized access');
    }
}
