@extends('layouts.sss-admin')
@section('title', 'Customers')
@section('subtitle', 'The people behind every piece you send.')
@section('actions')

@endsection
@section('content')
    <section class="panel table-panel" data-table>
        <div class="table-toolbar"><label class="search-input"><x-sss-admin.icon name="search" /><input data-search-input
                    type="search" placeholder="Search name, email, or city…" aria-label="Search customers"></label><button
                class="btn btn-light" data-export="sss-customers.csv"><x-sss-admin.icon name="download" /> Export
                customers</button></div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Location</th>
                        <th>Orders</th>
                        <th>Order value</th>
                        <th>Latest order</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (config('sss-admin-demo.orders') as $order)
                        <tr data-record
                            data-search="{{ strtolower($order['customer'] . ' ' . $order['email'] . ' ' . $order['city']) }}">
                            <td>
                                <div class="customer-identity"><span
                                        class="profile-avatar">{{ substr($order['customer'], 0, 1) }}</span>
                                    <div><strong>{{ $order['customer'] }}</strong><small>{{ $order['email'] }}</small></div>
                                </div>
                            </td>
                            <td>{{ $order['city'] }}</td>
                            <td>1</td>
                            <td>₹{{ number_format($order['total']) }}</td>
                            <td><a class="text-link"
                                    href="{{ route('admin.orders.show', $order['id']) }}">{{ $order['id'] }} ↗</a></td>
                        </tr>
                    @endforeach
                    <tr class="empty-row" hidden>
                        <td colspan="5">No customers match your search.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-foot"><span data-result-count aria-live="polite">6 customers</span><span>Sample customer
                directory</span></div>
    </section>
@endsection
