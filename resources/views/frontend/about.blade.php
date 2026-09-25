@extends('layouts.store')

@section('title', 'Our story — sss')
@section('page-key', 'about')

@section('content')
<div class="container-wide section"><div class="page-heading"><div class="crumb"><a href="index.html">Home</a> / Simple pieces. Strong style.</div><h1>Simple pieces. Strong style.</h1><p></p></div><div class="editorial"><div><img class="" src="https://images.unsplash.com/photo-1598554747436-c9293d6a588f?auto=format&amp;fit=crop&amp;w=900&amp;q=85" onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-1.svg') }}" alt="The Everyday Shirt" loading="lazy"></div><div class="editorial-copy"><span class="eyebrow">THIS IS SSS</span><h2>Clothes for<br>living in.</h2><p>We believe getting dressed should feel good. A favourite shirt. Denim that goes with everything. The soft layer you reach for on your way out.</p><p>sss brings together easy silhouettes and considered details, so you can spend less time deciding and more time being yourself.</p><a href="shop.html" class="text-link">Find your everyday ↗</a></div></div><div class="three-col section"><div><span class="eyebrow">01 / SIMPLICITY</span><h3>Fewer complications.</h3><p>Clean shapes that make getting dressed a little easier.</p></div><div><span class="eyebrow">02 / VERSATILITY</span><h3>More possibilities.</h3><p>Pieces that work together, from weekday to weekend.</p></div><div><span class="eyebrow">03 / INDIVIDUALITY</span><h3>Always your own.</h3><p>Make each piece part of your personal everyday.</p></div></div></div>
@endsection
