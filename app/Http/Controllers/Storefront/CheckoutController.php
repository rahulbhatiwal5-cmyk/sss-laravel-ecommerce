<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use App\Services\CartSummary;
use App\Services\GuestCartManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly GuestCartManager $carts,
        private readonly CartSummary $summary,
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $customer = $this->customer($request);
        // The customer middleware makes GuestCartManager resolve only user_id carts.
        $carts = $this->carts->currentCarts();
        $this->summary->loadRelations($carts);
        $cart = $this->summary->summarize($carts);

        if ($cart['items']->isEmpty()) {
            return to_route('store.cart')->with('error', 'Your bag is empty. Add a product before reviewing checkout.');
        }

        $addresses = $customer->addresses()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        return view('frontend.checkout', [
            ...$cart,
            'addresses' => $addresses,
            'selectedAddress' => $this->selectedAddress($request, $customer, $addresses),
        ]);
    }

    /**
     * A query parameter is only a selection hint; ownership is always checked
     * server-side against the authenticated customer's addresses.
     */
    private function selectedAddress(Request $request, User $customer, Collection $addresses): ?Address
    {
        $addressId = $request->query('address');

        if ($addressId !== null) {
            abort_unless(is_scalar($addressId) && ctype_digit((string) $addressId), 404);

            return $customer->addresses()
                ->whereKey((int) $addressId)
                ->firstOrFail();
        }

        return $addresses->firstWhere('is_default', true) ?? $addresses->first();
    }

    private function customer(Request $request): User
    {
        $customer = $request->user('web');

        abort_unless($customer instanceof User, 403);

        return $customer;
    }
}
