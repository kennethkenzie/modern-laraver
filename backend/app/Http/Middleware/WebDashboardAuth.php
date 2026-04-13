<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebDashboardAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('admin_token') || !session('admin_profile')) {
            return redirect('/')
                ->with('session_expired', 'Your session has expired. Please sign in again.');
        }

        return $next($request);
    }
}
