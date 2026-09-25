@extends('layouts.sss-admin')
@section('title', 'Orders')
@section('subtitle', 'Every order is the start of a good everyday.')
@section('actions')

@endsection
@section('content')
    <section class="panel table-panel" data-table>
        <div class="table-toolbar"><label class="search-input"><x-sss-admin.icon name="search" /><input data-search-input
                    type="search" placeholder="Search order or customer…" aria-label="Search orders"></label><select
                data-status-filter class="form-select" aria-label="Filter fulfilment">
                <option value="">All fulfilment statuses</option>
                <option>Processing</option>
                <option>Shipped</option>
                <option>Delivered</option>
                <option>Cancelled</option>
            </select><button class="btn btn-light" data-export="sss-orders.csv"><x-sss-admin.icon name="download" /> Export
                orders</button></div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Fulfilment</th>
                        <th><span class="visually-hidden">Details</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (config('sss-admin-demo.orders') as $order)
                        <tr data-record
                            data-search="{{ strtolower($order['id'] . ' ' . $order['customer'] . ' ' . $order['email']) }}"
                            data-status="{{ $order['status'] }}">
                            <td><a class="order-id"
                                    href="{{ route('admin.orders.show', $order['id']) }}">{{ $order['id'] }}</a></td>
                            <td><span class="cell-title">{{ $order['customer'] }}</span><small>{{ $order['email'] }}</small>
                            </td>
                            <td>{{ $order['date'] }}</td>
                            <td>₹{{ number_format($order['total']) }}</td>
                            <td><span
                                    class="status status-{{ strtolower($order['payment']) }}">{{ $order['payment'] }}</span>
                            </td>
                            <td><span
                                    class="status status-{{ strtolower($order['status']) }}">{{ $order['status'] }}</span>
                            </td>
                            <td><a class="row-action" href="{{ route('admin.orders.show', $order['id']) }}"
                                    aria-label="View order {{ $order['id'] }}">↗</a></td>
                        </tr>
                    @endforeach
                    <tr class="empty-row" hidden>
                        <td colspan="7">No matching orders. Try another search.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-foot"><span data-result-count aria-live="polite">6 orders</span><span>Sample order history</span>
        </div>
    </section>
@endsection
