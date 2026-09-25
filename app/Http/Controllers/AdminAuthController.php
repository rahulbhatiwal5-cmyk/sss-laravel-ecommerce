<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($response = $this->redirectAuthenticatedUser($request)) {
            return $response;
        }

        return view('sss-admin.auth.login');
    }

    public function store(AdminLoginRequest $request): RedirectResponse
    {
        if ($response = $this->redirectAuthenticatedUser($request)) {
            return $response;
        }

        $request->ensureIsNotRateLimited();

        if (! Auth::guard('web')->attempt($request->credentials(), $request->boolean('remember'))) {
            $request->incrementRateLimit();

            throw ValidationException::withMessages([
                'email' => 'Unable to sign in with these credentials.',
            ]);
        }

        $request->clearRateLimit();
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'You have been signed out.');
    }

    private function redirectAuthenticatedUser(Request $request): ?RedirectResponse
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return null;
        }

        $currentUser = $user->fresh();

        abort_unless(
            $currentUser && $currentUser->role === 'admin' && $currentUser->status === 'active',
            403,
        );

        return redirect()->route('admin.dashboard');
    }
}
