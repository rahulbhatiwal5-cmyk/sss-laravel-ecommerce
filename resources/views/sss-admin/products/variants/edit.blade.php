@extends('layouts.sss-admin')

@section('title', 'Edit variant')
@section('subtitle', 'Update SKU, inventory, status, and price overrides for '.$product->name.'.')
@section('topbar-label', 'Live variant data')

@section('actions')
    <a class="btn btn-light" href="{{ route('admin.products.variants.index', $product) }}">&larr; Manage variants</a>
    <a class="btn btn-light" href="{{ route('admin.products.edit', $product) }}">Edit product</a>
@endsection

@section('content')
    <div class="form-layout">
        <div>
            <section class="panel">
                <div class="panel-heading">
                    <div>
                        <h2>Variant options</h2>
                        <p>Size and color cannot be changed here. Create a separate variant for a different option combination.</p>
                    </div>
                </div>

                <div class="two-col">
                    <div class="field">
                        <label>Size</label>
                        <p class="form-control" aria-readonly="true">{{ $variant->size?->name ?? 'No size (default variant)' }}</p>
                    </div>
                    <div class="field">
                        <label>Color</label>
                        <p class="form-control" aria-readonly="true">{{ $variant->color?->name ?? 'No color (default variant)' }}</p>
                    </div>
                </div>
            </section>
        </div>

        <aside>
            @include('sss-admin.products.variants.partials.form', [
                'formAction' => route('admin.products.variants.update', [$product, $variant]),
                'formMethod' => 'PUT',
                'formHeading' => 'Variant inventory',
                'formDescription' => 'Change this variant without changing its size and color combination.',
                'submitLabel' => 'Save variant',
                'isCreating' => false,
            ])
        </aside>
    </div>
@endsection
