<?php

namespace App\Services;

use App\Models\Cart;
use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GuestCartManager
{
    private const SESSION_TOKEN_KEY = 'sss_guest_cart_token';

    public function __construct(private readonly Session $session)
    {
    }

    /**
     * Find the cart owned by this browser session without creating one.
     */
    public function current(): ?Cart
    {
        $token = $this->token();

        if ($token === null) {
            return null;
        }

        return Cart::query()
            ->whereNull('user_id')
            ->where('session_id', $token)
            ->orderBy('id')
            ->first();
    }

    public function itemCount(): int
    {
        $cart = $this->current();

        return $cart === null ? 0 : (int) $cart->items()->sum('quantity');
    }

    /**
     * Serialize mutations for one server-owned guest-cart token.
     *
     * @template T
     * @param  Closure(Cart): T  $callback
     * @return T|null
     */
    public function mutate(bool $createIfMissing, Closure $callback): mixed
    {
        $token = $createIfMissing ? $this->tokenForMutation() : $this->token();

        if ($token === null) {
            return null;
        }

        return Cache::lock('sss-guest-cart:'.hash('sha256', $token), 10)->block(5, function () use ($token, $createIfMissing, $callback): mixed {
            return DB::transaction(function () use ($token, $createIfMissing, $callback): mixed {
                $carts = Cart::query()
                    ->whereNull('user_id')
                    ->where('session_id', $token)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
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
                }

                return $callback($cart);
            }, 3);
        });
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
