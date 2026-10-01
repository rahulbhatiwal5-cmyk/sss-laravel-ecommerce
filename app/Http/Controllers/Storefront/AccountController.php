<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCustomerPasswordRequest;
use App\Http\Requests\UpdateCustomerProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        return view('frontend.account', [
            'customer' => $this->customer($request),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('frontend.profile', [
            'customer' => $this->customer($request),
        ]);
    }

    public function updateProfile(UpdateCustomerProfileRequest $request): RedirectResponse
    {
        $customer = $this->customer($request);

        $customer->update($request->safe()->only(['name', 'phone']));

        // Keep the current guard user in sync so the shared header immediately
        // shows the changed name on the redirect.
        Auth::guard('web')->setUser($customer->fresh());

        return to_route('store.profile')->with('status', 'Your profile has been updated.');
    }

    public function updatePassword(UpdateCustomerPasswordRequest $request): RedirectResponse
    {
        $customer = $this->customer($request);

        // User::password has a hashed cast, so the plaintext validated value is
        // never stored directly. No cart or wishlist data is touched here.
        $customer->update([
            'password' => $request->validated('password'),
        ]);

        Auth::guard('web')->setUser($customer->fresh());
        $request->session()->regenerate();

        return to_route('store.profile')->with('status', 'Your password has been changed.');
    }

    private function customer(Request $request): User
    {
        $customer = $request->user('web');

        abort_unless($customer instanceof User, 403);

        return $customer;
    }
}
