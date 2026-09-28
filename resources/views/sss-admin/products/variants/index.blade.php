@extends('layouts.sss-admin')

@section('title', 'Manage variants')
@section('subtitle', 'Set up size and color inventory for '.$product->name.'.')
@section('topbar-label', 'Live variant data')

@section('actions')
    <a class="btn btn-light" href="{{ route('admin.products.edit', $product) }}">Edit product</a>
    <a class="btn btn-light" href="{{ route('admin.products.index') }}">&larr; All products</a>
@endsection

@section('content')
    @if ($requiresTransition)
        <section class="note-panel">
            <span class="eyebrow">FIRST OPTION VARIANT</span>
            <h2>Transition the default inventory deliberately</h2>
            <p>
                The current default variant uses SKU <strong>{{ $defaultVariant->sku }}</strong> and has
                <strong>{{ $defaultVariant->stock }}</strong> units in stock. It is retained for any cart or order references;
                it will never be deleted by this transition.
            </p>
            <p>Before this first size or color variant is created, transfer all default stock to it or first reduce the default stock to zero. The default variant will then be inactive, so it cannot remain purchasable beside option variants.</p>
        </section>
    @endif

    <div class="form-layout">
        <div>
            <section class="panel table-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Existing variants</h2>
                        <p>Each SKU owns its own stock, status, and optional price overrides.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Options</th>
                                <th>SKU</th>
                                <th>Effective price</th>
                                <th>Stock</th>
                                <th>Low stock</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($product->variants as $variant)
                                @php
                                    $isDefaultVariant = $variant->size_id === null && $variant->color_id === null;
                                    $basePrice = $variant->price ?? $product->price;
                                    $salePrice = $variant->sale_price ?? ($variant->price === null ? $product->sale_price : null);
                                    $effectivePrice = $salePrice ?? $basePrice;
                                @endphp
                                <tr>
                                    <td>
                                        @if ($isDefaultVariant)
                                            <strong>Default / no options</strong>
                                            <small>Kept for inventory history</small>
                                        @else
                                            <strong>{{ $variant->size?->name ?? 'No size' }}</strong>
                                            <small>{{ $variant->color?->name ?? 'No color' }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $variant->sku }}</td>
                                    <td>
                                        <strong>₹{{ number_format((float) $effectivePrice, 2) }}</strong>
                                        @if ($variant->price === null)
                                            <small>Base price inherited: ₹{{ number_format((float) $product->price, 2) }}</small>
                                        @else
                                            <small>Variant base override: ₹{{ number_format((float) $variant->price, 2) }}</small>
                                        @endif
                                        @if ($salePrice !== null)
                                            <small>{{ $variant->sale_price === null ? 'Product sale inherited' : 'Variant sale override' }}: ₹{{ number_format((float) $salePrice, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $variant->stock }}</td>
                                    <td>{{ $variant->low_stock_limit }}</td>
                                    <td>
                                        <span class="status {{ $variant->is_active ? 'status-active' : 'status-draft' }}">
                                            {{ $variant->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a class="text-link" href="{{ route('admin.products.variants.edit', [$product, $variant]) }}">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr class="empty-row">
                                    <td colspan="7">No variants exist yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside>
            @include('sss-admin.products.variants.partials.form', [
                'variant' => null,
                'formAction' => route('admin.products.variants.store', $product),
                'formMethod' => 'POST',
                'formHeading' => 'Add variant',
                'formDescription' => 'Choose at least one active option. The same size and color pair can only appear once per product.',
                'submitLabel' => 'Add variant',
                'isCreating' => true,
            ])
        </aside>
    </div>
@endsection
