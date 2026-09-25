@extends('layouts.sss-admin')
@section('title', 'Products')
@section('subtitle', 'Considered pieces. An organized collection.')
@section('actions')
<a class="btn btn-dark" href="{{ route('admin.products.create') }}"><x-sss-admin.icon name="plus" /> Add product</a>
@endsection
@section('content')
<div class="mini-stats"><div><strong>6</strong> Total products</div><div><strong>5</strong> Active</div><div><strong>1</strong> Draft</div><div><strong>1</strong> Low stock</div></div>
<section class="panel table-panel" data-table><div class="table-toolbar"><label class="search-input"><x-sss-admin.icon name="search" /><input data-search-input type="search" placeholder="Search name or SKU…" aria-label="Search products"></label><select data-status-filter class="form-select" aria-label="Filter product status"><option value="">All statuses</option><option>Active</option><option>Draft</option></select><select data-category-filter class="form-select" aria-label="Filter collection"><option value="">All collections</option><option>Women</option><option>Men</option><option>Accessories</option></select><button data-export="sss-products.csv" class="btn btn-light"><x-sss-admin.icon name="download" /> Export</button></div><div class="table-responsive"><table class="admin-table"><thead><tr><th>Product</th><th>SKU</th><th>Collection</th><th>Price</th><th>Inventory</th><th>Status</th><th>Action</th></tr></thead><tbody>
@foreach(config('sss-admin-demo.products') as $product)
<tr data-record data-search="{{ strtolower($product['name'].' '.$product['sku']) }}" data-status="{{ $product['status'] }}" data-category="{{ $product['category'] }}"><td><a class="product-cell" href="{{ route('admin.products.edit', $product['id']) }}"><img src="{{ asset('sss-admin/images/product-'.$product['image'].'.svg') }}" alt="{{ $product['name'] }}"><strong>{{ $product['name'] }}</strong></a></td><td><span class="muted">{{ $product['sku'] }}</span></td><td>{{ $product['category'] }}</td><td>₹{{ number_format($product['price']) }}</td><td><span class="{{ $product['stock'] < 10 ? 'stock-low' : '' }}">{{ $product['stock'] }} in stock</span></td><td><span class="status status-{{ strtolower($product['status']) }}">{{ $product['status'] }}</span></td><td><a class="text-link" href="{{ route('admin.products.edit', $product['id']) }}">Edit ↗</a></td></tr>
@endforeach
<tr class="empty-row" hidden><td colspan="7">No products found. Try a different search or filter.</td></tr></tbody></table></div><div class="table-foot"><span data-result-count aria-live="polite">6 products</span><span>Sample catalogue</span></div></section>
@endsection
