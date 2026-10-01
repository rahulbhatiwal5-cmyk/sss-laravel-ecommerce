<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerWishlist
{
    public function activeCustomer(): ?User
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

    public function countFor(?User $customer): int
    {
        if ($customer === null) {
            return 0;
        }

        return Wishlist::query()
            ->where('user_id', $customer->getKey())
            ->count();
    }

    /**
     * @param  array<int, int|string>  $productIds
     * @return array<int, int>
     */
    public function itemIdsForProducts(User $customer, array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        return Wishlist::query()
            ->where('user_id', $customer->getKey())
            ->whereIn('product_id', $productIds)
            ->pluck('id', 'product_id')
            ->mapWithKeys(fn (mixed $wishlistId, mixed $productId): array => [(int) $productId => (int) $wishlistId])
            ->all();
    }

    /**
     * Save a product only if it is currently publicly visible.
     *
     * @return bool true when a row was created, false when it was already saved
     */
    public function save(User $customer, Product $product): bool
    {
        try {
            return DB::transaction(function () use ($customer, $product): bool {
                $lockedCustomer = User::query()
                    ->whereKey($customer->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $publicProduct = Product::query()
                    ->publiclyVisible(now())
                    ->whereKey($product->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $existing = Wishlist::query()
                    ->where('user_id', $lockedCustomer->getKey())
                    ->where('product_id', $publicProduct->getKey())
                    ->lockForUpdate()
                    ->first();

                if ($existing !== null) {
                    return false;
                }

                Wishlist::query()->create([
                    'user_id' => $lockedCustomer->getKey(),
                    'product_id' => $publicProduct->getKey(),
                ]);

                return true;
            }, 3);
        } catch (QueryException $exception) {
            // The unique(user_id, product_id) index is the final guard if an
            // out-of-process writer races this application-level lock.
            if (Wishlist::query()
                ->where('user_id', $customer->getKey())
                ->where('product_id', $product->getKey())
                ->exists()) {
                return false;
            }

            throw $exception;
        }
    }

    public function remove(User $customer, Wishlist $wishlist): void
    {
        DB::transaction(function () use ($customer, $wishlist): void {
            $lockedCustomer = User::query()
                ->whereKey($customer->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $ownedItem = Wishlist::query()
                ->where('user_id', $lockedCustomer->getKey())
                ->whereKey($wishlist->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $ownedItem->delete();
        }, 3);
    }
}
