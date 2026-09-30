<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressFormRequest;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $this->customer($request);

        return view('frontend.addresses', [
            'customer' => $customer,
            'addresses' => $customer->addresses()
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $customer = $this->customer($request);

        return view('frontend.addresses.create', [
            'customer' => $customer,
            'address' => null,
            'isFirstAddress' => ! $customer->addresses()->exists(),
        ]);
    }

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $customer = $this->customer($request);
        $attributes = $this->attributes($request);

        try {
            DB::transaction(function () use ($customer, $attributes): void {
                $lockedCustomer = $this->lockCustomer($customer);
                $isFirstAddress = ! Address::query()
                    ->where('user_id', $lockedCustomer->getKey())
                    ->lockForUpdate()
                    ->exists();
                $makeDefault = $isFirstAddress || $attributes['is_default'];

                if ($makeDefault) {
                    Address::query()
                        ->where('user_id', $lockedCustomer->getKey())
                        ->update(['is_default' => false]);
                }

                Address::query()->create([
                    ...$attributes,
                    'user_id' => $lockedCustomer->getKey(),
                    'is_default' => $makeDefault,
                ]);
            }, 3);
        } catch (QueryException $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Your address could not be saved. Please try again.');
        }

        return to_route('store.addresses')->with('status', 'Address saved.');
    }

    public function edit(Request $request, Address $address): View
    {
        $customer = $this->customer($request);

        return view('frontend.addresses.edit', [
            'customer' => $customer,
            'address' => $this->ownedAddress($customer, $address),
            'isFirstAddress' => false,
        ]);
    }

    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        $customer = $this->customer($request);
        // Return 404 before any mutation when the URL belongs to another customer.
        $this->ownedAddress($customer, $address);
        $attributes = $this->attributes($request);

        try {
            DB::transaction(function () use ($customer, $address, $attributes): void {
                $lockedCustomer = $this->lockCustomer($customer);
                $lockedAddress = $this->ownedAddress($lockedCustomer, $address, true);
                $makeDefault = $attributes['is_default'];

                if ($makeDefault) {
                    Address::query()
                        ->where('user_id', $lockedCustomer->getKey())
                        ->where('id', '!=', $lockedAddress->getKey())
                        ->update(['is_default' => false]);
                } elseif ($lockedAddress->is_default) {
                    $fallback = Address::query()
                        ->where('user_id', $lockedCustomer->getKey())
                        ->where('id', '!=', $lockedAddress->getKey())
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->first();

                    if ($fallback === null) {
                        // A customer with one address must retain a default.
                        $makeDefault = true;
                    } else {
                        Address::query()
                            ->where('user_id', $lockedCustomer->getKey())
                            ->update(['is_default' => false]);
                        $fallback->update(['is_default' => true]);
                    }
                }

                $lockedAddress->update([
                    ...$attributes,
                    'is_default' => $makeDefault,
                ]);
            }, 3);
        } catch (QueryException $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Your address could not be updated. Please try again.');
        }

        return to_route('store.addresses')->with('status', 'Address updated.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $customer = $this->customer($request);
        // Orders contain independent shipping-address snapshots, not address_id FKs.
        // This scoped lookup still prevents another customer from deleting this row.
        $this->ownedAddress($customer, $address);

        try {
            DB::transaction(function () use ($customer, $address): void {
                $lockedCustomer = $this->lockCustomer($customer);
                $lockedAddress = $this->ownedAddress($lockedCustomer, $address, true);
                $wasDefault = $lockedAddress->is_default;

                $lockedAddress->delete();

                if ($wasDefault) {
                    $this->assignDeterministicDefault($lockedCustomer);
                }
            }, 3);
        } catch (QueryException $exception) {
            report($exception);

            return back()->with('error', 'Your address could not be removed. Please try again.');
        }

        return to_route('store.addresses')->with('status', 'Address removed.');
    }

    public function setDefault(Request $request, Address $address): RedirectResponse
    {
        $customer = $this->customer($request);
        $this->ownedAddress($customer, $address);

        try {
            DB::transaction(function () use ($customer, $address): void {
                $lockedCustomer = $this->lockCustomer($customer);
                $lockedAddress = $this->ownedAddress($lockedCustomer, $address, true);

                Address::query()
                    ->where('user_id', $lockedCustomer->getKey())
                    ->update(['is_default' => false]);
                $lockedAddress->update(['is_default' => true]);
            }, 3);
        } catch (QueryException $exception) {
            report($exception);

            return back()->with('error', 'Your default address could not be changed. Please try again.');
        }

        return to_route('store.addresses')->with('status', 'Default address updated.');
    }

    private function customer(Request $request): User
    {
        $customer = $request->user('web');

        abort_unless($customer instanceof User, 403);

        return $customer;
    }

    private function lockCustomer(User $customer): User
    {
        return User::query()
            ->whereKey($customer->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function ownedAddress(User $customer, Address $address, bool $lockForUpdate = false): Address
    {
        $query = Address::query()
            ->where('user_id', $customer->getKey())
            ->whereKey($address->getKey());

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->firstOrFail();
    }

    /**
     * @return array<string, string|bool|null>
     */
    private function attributes(AddressFormRequest $request): array
    {
        return $request->safe()->only([
            'type',
            'name',
            'phone',
            'address_line_1',
            'address_line_2',
            'landmark',
            'city',
            'state',
            'postal_code',
            'country',
            'is_default',
        ]);
    }

    private function assignDeterministicDefault(User $customer): void
    {
        $fallback = Address::query()
            ->where('user_id', $customer->getKey())
            ->orderBy('id')
            ->lockForUpdate()
            ->first();

        if ($fallback === null) {
            return;
        }

        Address::query()
            ->where('user_id', $customer->getKey())
            ->update(['is_default' => false]);
        $fallback->update(['is_default' => true]);
    }
}
