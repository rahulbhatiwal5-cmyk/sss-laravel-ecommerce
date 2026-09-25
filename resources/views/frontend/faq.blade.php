@extends('layouts.store')

@section('title', 'Frequently asked questions — sss')
@section('page-key', 'faq')

@section('content')
<div class="container-wide section"><div class="page-heading"><div class="crumb"><a href="index.html">Home</a> / A few good answers.</div><h1>A few good answers.</h1><p></p></div><div class="reading"><details class="faq"><summary>How do I find my size?</summary><p>Start with our size guide and compare the garment measurements with a piece you already own. Fit can vary by style; check the product description.</p></details><details class="faq"><summary>Where is my order?</summary><p>Open Track your order and enter your order number and checkout email. This template tracks demo orders created in the current browser.</p></details><details class="faq"><summary>How much does delivery cost?</summary><p>The sample storefront offers free standard shipping on discounted subtotals of ₹2,999 or more. Otherwise, standard shipping is ₹99.</p></details><details class="faq"><summary>Can I return an item?</summary><p>The sample policy allows eligible unworn items with tags to be returned within 14 days of delivery. Contact the store before sending anything back.</p></details><details class="faq"><summary>Can I change my order?</summary><p>Contact the store as soon as possible. Changes depend on whether your order has already been packed or dispatched.</p></details><details class="faq"><summary>Which payment methods are available?</summary><p>This frontend includes a cash-on-delivery demo. Payment gateways such as Razorpay or Stripe must be connected in the Laravel backend.</p></details><div class="panel mt-4"><h3>Still have a question?</h3><a href="contact.html" class="text-link">Let’s talk ↗</a></div></div></div>
@endsection

