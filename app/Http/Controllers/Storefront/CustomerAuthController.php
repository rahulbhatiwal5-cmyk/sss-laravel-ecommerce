<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerLoginRequest;
use App\Http\Requests\StoreCustomerRegistrationRequest;
use App\Models\User;
use App\Services\GuestCartManager;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CustomerAuthController extends Controller
{
    public function __construct(private readonly GuestCartManager $carts)
    {
    }

    public function createLogin(Request $request): View|RedirectResponse
    {
        if ($response = $this->redirectAuthenticatedCustomer($request)) {
            return $response;
        }

        return view('frontend.login');
    }

    public function storeLogin(CustomerLoginRequest $request): RedirectResponse
    {
        if ($response = $this->redirectAuthenticatedCustomer($request)) {
            return $response;
        }

        $request->ensureIsNotRateLimited();

        if (! Auth::guard('web')->attempt($request->credentials(), $request->boolean('remember'))) {
            $request->incrementRateLimit();

            throw ValidationException::withMessages([
                'email' => 'Unable to sign in with these credentials.',
            ]);
        }

        /** @var User $customer */
        $customer = Auth::guard('web')->user();

        if ($response = $this->mergeCartOrRestoreGuestSession($request, $customer)) {
            return $response;
        }

        $request->clearRateLimit();
        $request->session()->regenerate();

        return $this->redirectAfterAuthentication($request)->with('status', 'Welcome back.');
    }

    public function createRegistration(Request $request): View|RedirectResponse
    {
        if ($response = $this->redirectAuthenticatedCustomer($request)) {
            return $response;
        }

        return view('frontend.register');
    }

    public function storeRegistration(StoreCustomerRegistrationRequest $request): RedirectResponse
    {
        if ($response = $this->redirectAuthenticatedCustomer($request)) {
            return $response;
        }

        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => $request->validated('password'),
            'role' => 'customer',
            'status' => 'active',
        ]);

        try {
            $this->carts->mergeGuestCartIntoCustomer($user);
        } catch (LockTimeoutException) {
            return to_route('store.login')->with('error', 'Your account was created, but your bag is busy. Please sign in again to continue.');
        } catch (Throwable $exception) {
            report($exception);

            return to_route('store.login')->with('error', 'Your account was created, but we could not complete sign-in. Please sign in again. Your bag was not changed.');
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return $this->redirectAfterAuthentication($request)->with('status', 'Your account has been created.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();
        $currentUser = $user?->fresh();

        // Admins use their own admin logout route even though both areas share the web guard.
        abort_unless($currentUser && $currentUser->role === 'customer', 403);

        // This affects only the browser token. The user-owned database cart remains saved.
        $this->carts->forgetGuestToken();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store.login')->with('status', 'You have been signed out.');
    }

    private function redirectAuthenticatedCustomer(Request $request): ?RedirectResponse
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return null;
        }

        $currentUser = $user->fresh();

        abort_unless(
            $currentUser && $currentUser->role === 'customer' && $currentUser->status === 'active',
            403,
        );

        Auth::guard('web')->setUser($currentUser);

        return $this->redirectAfterAuthentication($request);
    }

    private function mergeCartOrRestoreGuestSession(Request $request, User $customer): ?RedirectResponse
    {
        try {
            $this->carts->mergeGuestCartIntoCustomer($customer);
        } catch (LockTimeoutException) {
            Auth::guard('web')->logout();

            return back()->withInput()->withErrors([
                'email' => 'We could not complete sign in right now. Your bag was not changed. Please try again.',
            ]);
        } catch (Throwable $exception) {
            report($exception);
            Auth::guard('web')->logout();

            return back()->withInput()->withErrors([
                'email' => 'We could not complete sign in right now. Your bag was not changed. Please try again.',
            ]);
        }

        return null;
    }

    private function redirectAfterAuthentication(Request $request): RedirectResponse
    {
        $intended = $request->session()->pull('url.intended');

        if (is_string($intended) && $this->isStorefrontDestination($intended, $request)) {
            return redirect()->to($intended);
        }

        return redirect()->route('store.home');
    }

    private function isStorefrontDestination(string $url, Request $request): bool
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return false;
        }

        if (isset($parts['scheme']) && ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        if (isset($parts['host']) && ($parts['host'] !== $request->getHost()
            || (isset($parts['port']) && (int) $parts['port'] !== $request->getPort()))) {
            return false;
        }

        $path = '/'.ltrim($parts['path'] ?? '/', '/');

        return ! str_starts_with($path, '/admin');
    }
}
