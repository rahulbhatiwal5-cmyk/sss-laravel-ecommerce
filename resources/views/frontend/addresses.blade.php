@extends('layouts.store')

@section('title', 'My addresses — sss')
@section('page-key', 'customer-addresses')

@section('content')
    <div class="container-wide section" data-server-addresses>
        <div class="page-heading">
            <div class="crumb"><a href="{{ route('store.home') }}">Home</a> / My account</div>
            <h1>My account</h1>
            <p>Keep your delivery details ready for later checkout.</p>
        </div>

        <div class="account-layout">
            <aside class="account-nav">
                <div class="avatar">{{ strtoupper(substr($customer->name, 0, 1)) }}</div>
                <h3>Hello, {{ $customer->name }}.</h3>
                <p class="muted">Your everyday, organized.</p>
                <a href="{{ route('store.account') }}">Overview <span>↗</span></a>
                <a href="{{ route('store.orders') }}">My orders <span>↗</span></a>
                <a class="active" href="{{ route('store.addresses') }}" aria-current="page">My addresses <span>↗</span></a>
                <a href="{{ route('store.profile') }}">Profile settings <span>↗</span></a>
                <a href="{{ route('store.wishlist') }}">Wishlist <span>↗</span></a>
                <form method="POST" action="{{ route('store.logout') }}">
                    @csrf
                    <button class="text-button" type="submit">Sign out ↗</button>
                </form>
            </aside>

            <div>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <h2 class="mb-1">Delivery addresses</h2>
                        <p class="muted mb-0">Choose one default address for a faster checkout later.</p>
                    </div>
                    <a class="btn btn-dark" href="{{ route('store.addresses.create') }}">Add address ↗</a>
                </div>

                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif

                @forelse ($addresses as $address)
                    <article class="panel mb-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                            <div>
                                @if ($address->is_default)
                                    <span class="eyebrow">DEFAULT ADDRESS</span>
                                @else
                                    <span class="eyebrow">{{ $address->type }}</span>
                                @endif
                                <h3 class="mt-2">{{ $address->name }}</h3>
                                <p class="mb-0">
                                    {{ $address->address_line_1 }}<br>
                                    @if ($address->address_line_2)
                                        {{ $address->address_line_2 }}<br>
                                    @endif
                                    @if ($address->landmark)
                                        {{ $address->landmark }}<br>
                                    @endif
                                    {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}<br>
                                    {{ $address->country }}<br>
                                    <span class="muted">{{ $address->phone }}</span>
                                </p>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                @unless ($address->is_default)
                                    <form method="POST" action="{{ route('store.addresses.default', $address) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-outline-dark" type="submit">Set default</button>
                                    </form>
                                @endunless
                                <a class="btn btn-outline-dark" href="{{ route('store.addresses.edit', $address) }}">Edit</a>
                                <form method="POST" action="{{ route('store.addresses.destroy', $address) }}"
                                    onsubmit="return confirm('Remove this saved address?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-button" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="panel empty">
                        <h2>No saved addresses yet.</h2>
                        <p>Add a delivery address now; checkout is implemented in a later step.</p>
                        <a class="btn btn-dark" href="{{ route('store.addresses.create') }}">Add your first address ↗</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
