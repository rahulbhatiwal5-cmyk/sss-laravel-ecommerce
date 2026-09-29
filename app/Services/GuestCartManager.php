<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use LogicException;

class GuestCartManager
{
    private const SESSION_TOKEN_KEY = 'sss_guest_cart_token';

    private const MAX_CART_ITEM_QUANTITY = 4294967295;

    public function __construct(private readonly Session $session)
    {
    }

    /**
     * Return every cart belonging to the current cart owner without creating one.
     *
     * Account-cart duplicates are displayed together until the next authenticated
     * merge or mutation consolidates them. This avoids hiding existing cart data.
     *
     * @return Collection<int, Cart>
     */
    public function currentCarts(): Collection
    {
        $customer = $this->activeCustomer();

        if ($customer !== null) {
            return Cart::query()
                ->where('user_id', $customer->getKey())
                ->orderBy('id')
                ->get();
        }

        $token = $this->token();

        if ($token === null) {
            return collect();
        }

        return Cart::query()
            ->whereNull('user_id')
            ->where('session_id', $token)
            ->orderBy('id')
            ->get();
    }

    /**
     * Return the canonical cart for callers that need one cart instance.
     */
    public function current(): ?Cart
    {
        return $this->currentCarts()->first();
    }

    public function itemCount(): int
    {
        $cartIds = $this->currentCarts()->pluck('id')->all();

        if ($cartIds === []) {
            return 0;
        }

        return (int) CartItem::query()
            ->whereIn('cart_id', $cartIds)
            ->sum('quantity');
    }

    /**
     * Serialize a mutation for the current owner. Active customers use their
     * account cart; every other visitor uses only the server-owned guest token.
     *
     * @template T
     * @param  Closure(Cart): T  $callback
     * @return T|null
     */
    public function mutate(bool $createIfMissing, Closure $callback): mixed
    {
        $customer = $this->activeCustomer();

        if ($customer !== null) {
            return $this->mutateCustomer($customer, $createIfMissing, $callback);
        }

        return $this->mutateGuest($createIfMissing, $callback);
    }

    /**
     * Merge the current browser's guest cart into an active customer account.
     *
     * The cart rows and their line rows are locked before consolidation. Lines
     * are deliberately not checked against current visibility or stock here: a
     * customer must be able to see and adjust an unavailable or over-stock line.
     */
    public function mergeGuestCartIntoCustomer(User $customer): ?Cart
    {
        if ($customer->role !== 'customer' || $customer->status !== 'active') {
            throw new LogicException('Only active customers can own an account cart.');
        }

        $guestToken = $this->token();
        $lockKeys = [$this->customerLockKey($customer->getKey())];

        if ($guestToken !== null) {
            $lockKeys[] = $this->guestLockKey($guestToken);
        }

        $cart = $this->withLocks($lockKeys, function () use ($customer, $guestToken): ?Cart {
            return DB::transaction(function () use ($customer, $guestToken): ?Cart {
                $customerCarts = $this->lockedCustomerCarts($customer->getKey());
                $guestCarts = $guestToken === null ? collect() : $this->lockedGuestCarts($guestToken);

                if ($customerCarts->isNotEmpty()) {
                    return $this->consolidateLockedCarts(
                        $customerCarts->concat($guestCarts)->values(),
                        $customerCarts->first(),
                    );
                }

                if ($guestCarts->isEmpty()) {
                    return null;
                }

                $canonicalCart = $this->consolidateLockedCarts($guestCarts, $guestCarts->first());
                $canonicalCart->update([
                    'user_id' => $customer->getKey(),
                    'session_id' => null,
                ]);

                return $canonicalCart;
            }, 3);
        });

        // The account cart now owns every successfully handled guest line.
        // Clearing this token also guarantees that a later logout starts blank.
        $this->forgetGuestToken();

        return $cart;
    }

    /**
     * Remove the browser-only ownership token. Account carts remain untouched.
     */
    public function forgetGuestToken(): void
    {
        $this->session->forget(self::SESSION_TOKEN_KEY);
    }

    /**
     * @template T
     * @param  Closure(Cart): T  $callback
     * @return T|null
     */
    private function mutateCustomer(User $customer, bool $createIfMissing, Closure $callback): mixed
    {
        return $this->withLocks([$this->customerLockKey($customer->getKey())], function () use ($customer, $createIfMissing, $callback): mixed {
            return DB::transaction(function () use ($customer, $createIfMissing, $callback): mixed {
                $carts = $this->lockedCustomerCarts($customer->getKey());
                $cart = $carts->first();

                if ($cart === null && ! $createIfMissing) {
                    return null;
                }

                if ($cart === null) {
                    $cart = Cart::query()->create([
                        'user_id' => $customer->getKey(),
                        'session_id' => null,
                    ]);

                    $cart = Cart::query()
                        ->whereKey($cart->getKey())
                        ->lockForUpdate()
                        ->firstOrFail();
                } else {
                    $cart = $this->consolidateLockedCarts($carts, $cart);
                }

                return $callback($cart);
            }, 3);
        });
    }

    /**
     * @template T
     * @param  Closure(Cart): T  $callback
     * @return T|null
     */
    private function mutateGuest(bool $createIfMissing, Closure $callback): mixed
    {
        $token = $createIfMissing ? $this->tokenForMutation() : $this->token();

        if ($token === null) {
            return null;
        }

        return $this->withLocks([$this->guestLockKey($token)], function () use ($token, $createIfMissing, $callback): mixed {
            return DB::transaction(function () use ($token, $createIfMissing, $callback): mixed {
                $carts = $this->lockedGuestCarts($token);
                $cart = $carts->first();

                if ($cart === null && ! $createIfMissing) {
                    return null;
                }

                if ($cart === null) {
                    $cart = Cart::query()->create([
                        'user_id' => null,
                        'session_id' => $token,
                    ]);

                    $cart = Cart::query()
                        ->whereKey($cart->getKey())
                        ->lockForUpdate()
                        ->firstOrFail();
                } else {
                    $cart = $this->consolidateLockedCarts($carts, $cart);
                }

                return $callback($cart);
            }, 3);
        });
    }

    /**
     * Move all rows into $canonicalCart, merge exact product/variant matches,
     * then delete only the source carts whose lines were safely handled.
     *
     * @param  Collection<int, Cart>  $carts
     */
    private function consolidateLockedCarts(Collection $carts, Cart $canonicalCart): Cart
    {
        $cartIds = $carts->pluck('id')->all();

        if ($cartIds === []) {
            return $canonicalCart;
        }

        $items = CartItem::query()
            ->whereIn('cart_id', $cartIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
        $keptItems = [];

        foreach ($items as $item) {
            $identity = $this->lineIdentity($item);

            if (! array_key_exists($identity, $keptItems)) {
                if ((string) $item->cart_id !== (string) $canonicalCart->getKey()) {
                    $item->update(['cart_id' => $canonicalCart->getKey()]);
                }

                $keptItems[$identity] = $item;

                continue;
            }

            /** @var CartItem $keptItem */
            $keptItem = $keptItems[$identity];
            $quantity = $keptItem->quantity + $item->quantity;

            if ($quantity > self::MAX_CART_ITEM_QUANTITY) {
                throw new LogicException('Cart line quantity exceeds the supported limit.');
            }

            $keptItem->update(['quantity' => $quantity]);
            $item->delete();
        }

        foreach ($carts as $cart) {
            if ((string) $cart->getKey() !== (string) $canonicalCart->getKey()) {
                $cart->delete();
            }
        }

        return $canonicalCart;
    }

    private function lineIdentity(CartItem $item): string
    {
        return 'product:'.($item->product_id ?? 'null').'|variant:'.($item->product_variant_id ?? 'null');
    }

    /**
     * @return Collection<int, Cart>
     */
    private function lockedCustomerCarts(int|string $userId): Collection
    {
        return Cart::query()
            ->where('user_id', $userId)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    /**
     * @return Collection<int, Cart>
     */
    private function lockedGuestCarts(string $token): Collection
    {
        return Cart::query()
            ->whereNull('user_id')
            ->where('session_id', $token)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    private function activeCustomer(): ?User
    {
        $user = Auth::guard('web')->user();

        if (! $user instanceof User) {
            return null;
        }

        $customer = $user->fresh();

        if ($customer === null || $customer->role !== 'customer' || $customer->status !== 'active') {
            return null;
        }

        Auth::guard('web')->setUser($customer);

        return $customer;
    }

    /**
     * Acquire all logical cart locks in one deterministic order.
     *
     * @template T
     * @param  array<int, string>  $keys
     * @param  Closure(): T  $callback
     * @return T
     */
    private function withLocks(array $keys, Closure $callback): mixed
    {
        sort($keys, SORT_STRING);

        return $this->withLocksAt($keys, 0, $callback);
    }

    /**
     * @template T
     * @param  array<int, string>  $keys
     * @param  Closure(): T  $callback
     * @return T
     */
    private function withLocksAt(array $keys, int $position, Closure $callback): mixed
    {
        if (! isset($keys[$position])) {
            return $callback();
        }

        return Cache::lock($keys[$position], 10)->block(5, function () use ($keys, $position, $callback): mixed {
            return $this->withLocksAt($keys, $position + 1, $callback);
        });
    }

    private function customerLockKey(int|string $userId): string
    {
        return 'sss-customer-cart:'.hash('sha256', (string) $userId);
    }

    private function guestLockKey(string $token): string
    {
        return 'sss-guest-cart:'.hash('sha256', $token);
    }

    private function token(): ?string
    {
        $token = $this->session->get(self::SESSION_TOKEN_KEY);

        return is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token) === 1
            ? $token
            : null;
    }

    private function tokenForMutation(): string
    {
        $token = $this->token();

        if ($token !== null) {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        $this->session->put(self::SESSION_TOKEN_KEY, $token);

        return $token;
    }
}
