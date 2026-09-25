<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return redirect()->guest(route('admin.login'));
        }

        // Refresh on every protected request so role/status changes take effect immediately.
        $currentUser = $user->fresh();

        abort_unless(
            $currentUser && $currentUser->role === 'admin' && $currentUser->status === 'active',
            403,
        );

        Auth::guard('web')->setUser($currentUser);

        return $next($request);
    }
}
