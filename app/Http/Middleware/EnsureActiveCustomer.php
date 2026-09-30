<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return redirect()->guest(route('store.login'));
        }

        // Role/status may change after login, so check the current database row
        // on every protected customer request instead of trusting the session user alone.
        $currentUser = $user->fresh();

        abort_unless(
            $currentUser && $currentUser->role === 'customer' && $currentUser->status === 'active',
            403,
        );

        Auth::guard('web')->setUser($currentUser);

        return $next($request);
    }
}
