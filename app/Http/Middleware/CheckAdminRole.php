<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\RoosterController;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->hasRole('admin')) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'Unauthorized access');
    }
}
