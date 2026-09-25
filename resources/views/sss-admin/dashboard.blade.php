@extends('layouts.sss-admin')
@section('title', 'Overview')
@section('subtitle', 'A clear view of your store. One good day at a time.')
@section('actions')
    <span class="date-label">14–20 Sep, 2026</span>
@endsection
@section('content')
    <div class="welcome-banner">
        <div><span class="eyebrow">THE EVERYDAY OVERVIEW</span>
            <h2>Good afternoon, store owner.</h2>
            <p>Here’s how things are shaping up this week.</p>
        </div><a class="btn btn-dark" href="{{ route('admin.products.create') }}">Add a new piece <x-sss-admin.icon
                name="plus" /></a>
    </div>
    <div class="stats-grid">
        <article class="stat-card">
            <div class="stat-label">Total revenue<x-sss-admin.icon name="orders" /></div><strong>₹3,06,000</strong>
            <div><span class="trend">↗ 12.8%</span><span class="muted small"> vs. previous week</span></div>
        </article>
        <article class="stat-card">
            <div class="stat-label">Orders placed<x-sss-admin.icon name="orders" /></div><strong>128</strong>
            <div><span class="trend">↗ 8.2%</span><span class="muted small"> vs. previous week</span></div>
        </article>
        <article class="stat-card">
            <div class="stat-label">New customers<x-sss-admin.icon name="customers" /></div><strong>46</strong>
            <div><span class="trend">↗ 6.4%</span><span class="muted small"> vs. previous week</span></div>
        </article>
        <article class="stat-card">
            <div class="stat-label">Average order value<x-sss-admin.icon name="products" /></div><strong>₹2,391</strong>
            <div><span class="trend">↗ 4.2%</span><span class="muted small"> vs. previous week</span></div>
        </article>
    </div>
    <div class="dashboard-grid">
        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Revenue overview</h2>
                    <p>14–20 September 2026 · illustrative data</p>
                </div><span class="legend-dot">This week</span>
            </div><svg class="revenue-chart" viewBox="0 0 600 260" role="img" aria-labelledby="chart-title chart-desc">
                <title id="chart-title">Illustrative weekly revenue</title>
                <desc id="chart-desc">Monday ₹24,000, Tuesday ₹35,000, Wednesday ₹31,000, Thursday ₹48,000, Friday ₹43,000,
                    Saturday ₹65,000, Sunday ₹60,000.</desc>
                <defs>
                    <linearGradient id="area" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#818b64" stop-opacity=".24" />
                        <stop offset="100%" stop-color="#818b64" stop-opacity="0" />
                    </linearGradient>
                </defs>
                <line x1="40" x2="560" y1="28" y2="28" stroke="#e6e7df" /><text x="0" y="32"
                    fill="#86897b" font-size="10">80k</text>
                <line x1="40" x2="560" y1="76" y2="76" stroke="#e6e7df" /><text x="0" y="80"
                    fill="#86897b" font-size="10">60k</text>
                <line x1="40" x2="560" y1="124" y2="124" stroke="#e6e7df" /><text x="0" y="128"
                    fill="#86897b" font-size="10">40k</text>
                <line x1="40" x2="560" y1="172" y2="172" stroke="#e6e7df" /><text x="0" y="176"
                    fill="#86897b" font-size="10">20k</text>
                <line x1="40" x2="560" y1="220" y2="220" stroke="#e6e7df" /><text x="0" y="224"
                    fill="#86897b" font-size="10">0k</text>
                <polygon
                    points="45,220 45,162.4 128,136.0 211,145.60000000000002 294,104.80000000000001 377,116.8 460,64.0 543,76.0 543,220"
                    fill="url(#area)" />
                <polyline
                    points="45,162.4 128,136.0 211,145.60000000000002 294,104.80000000000001 377,116.8 460,64.0 543,76.0"
                    fill="none" stroke="#718054" stroke-width="3" />
                <circle cx="45" cy="162.4" r="4" fill="#718054" /><text x="45" y="248" text-anchor="middle"
                    fill="#86897b" font-size="10">Mon</text>
                <circle cx="128" cy="136.0" r="4" fill="#718054" /><text x="128" y="248" text-anchor="middle"
                    fill="#86897b" font-size="10">Tue</text>
                <circle cx="211" cy="145.60000000000002" r="4" fill="#718054" /><text x="211" y="248"
                    text-anchor="middle" fill="#86897b" font-size="10">Wed</text>
                <circle cx="294" cy="104.80000000000001" r="4" fill="#718054" /><text x="294" y="248"
                    text-anchor="middle" fill="#86897b" font-size="10">Thu</text>
                <circle cx="377" cy="116.8" r="4" fill="#718054" /><text x="377" y="248" text-anchor="middle"
                    fill="#86897b" font-size="10">Fri</text>
                <circle cx="460" cy="64.0" r="4" fill="#718054" /><text x="460" y="248" text-anchor="middle"
                    fill="#86897b" font-size="10">Sat</text>
                <circle cx="543" cy="76.0" r="4" fill="#718054" /><text x="543" y="248" text-anchor="middle"
                    fill="#86897b" font-size="10">Sun</text>
            </svg>
            <details class="chart-data">
                <summary>View chart data</summary>
                <p>Mon ₹24,000 · Tue ₹35,000 · Wed ₹31,000 · Thu ₹48,000 · Fri ₹43,000 · Sat ₹65,000 · Sun ₹60,000</p>
            </details>
        </section>
        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Collection mix</h2>
                    <p>Share of weekly sales</p>
                </div>
            </div>
            <div class="donut" role="img" aria-label="Women 48%, Men 32%, Accessories 14%, Kids 6%">
                <div><strong>128</strong><small>ORDERS</small></div>
            </div>
            <div class="mix-legend">
                <div><span><i style="background:#697652"></i>Women</span><strong>48%</strong></div>
                <div><span><i style="background:#a5ad8e"></i>Men</span><strong>32%</strong></div>
                <div><span><i style="background:#c4bca5"></i>Accessories</span><strong>14%</strong></div>
                <div><span><i style="background:#e0dccb"></i>Kids</span><strong>6%</strong></div>
            </div>
        </section>
    </div>
    <section class="panel table-panel" data-table>
        <div class="panel-heading">
            <div>
                <h2>Recent orders</h2>
                <p>A snapshot of what’s happening in your store.</p>
            </div><a class="text-link" href="{{ route('admin.orders.index') }}">All orders ↗</a>
        </div>
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
                            <td><span
                                    class="cell-title">{{ $order['customer'] }}</span><small>{{ $order['email'] }}</small>
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
    </section>
    <div class="two-col">
        <section class="panel">
            <div class="panel-heading">
                <h2>Pieces to keep an eye on</h2><a href="{{ route('admin.products.index') }}"
                    class="text-link">Inventory ↗</a>
            </div>
            <div class="attention-row"><span class="attention-icon">!</span>
                <div><strong>The Weekend Blazer</strong><small>Only 7 pieces remaining</small></div><a
                    href="{{ route('admin.products.edit', 3) }}" class="text-link">Review</a>
            </div>
            <div class="attention-row"><span class="attention-icon">!</span>
                <div><strong>Soft Knit Cardigan</strong><small>Out of stock · currently a draft</small></div><a
                    href="{{ route('admin.products.edit', 5) }}" class="text-link">Review</a>
            </div>
        </section>
        <section class="note-panel"><span class="eyebrow">A LITTLE STOREKEEPING</span>
            <h2>Small details.<br>Better experiences.</h2>
            <p>Keep your product photos, fit notes, and stock levels fresh. Your next customer will thank you.</p><a
                class="text-link" href="{{ route('admin.products.index') }}">Review your collection ↗</a>
        </section>
    </div>
@endsection
