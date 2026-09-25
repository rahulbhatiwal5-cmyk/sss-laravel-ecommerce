@extends('layouts.store')

@section('title', 'Everyday, elevated - sss')
@section('page-key', 'index')

@section('content')
        <section class="hero">
            <div class="hero-copy"><span class="eyebrow">THE NEW SEASON / COLLECTION 01</span>
                <h1>Everyday,<br><em>elevated.</em></h1>
                <p>Considered essentials. Effortless style.<br>Find the pieces that feel like you.</p><a
                    href="shop.html" class="btn btn-dark">Explore the collection <span>↗</span></a>
                <div class="hero-foot"><span>LESS NOISE. MORE YOU.</span><span>01 — 03</span></div>
            </div>
            <div class="hero-photo"><img
                    src="https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&amp;fit=crop&amp;w=1400&amp;q=90"
                    alt="Neutral outfit styled for the new season"
                    onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-3.svg') }}'">
                <div class="hero-caption">THE ART OF GETTING DRESSED <span>sss / 2026</span></div>
            </div>
        </section>
        <div class="benefits container-wide"><span>↗ &nbsp; Free shipping over ₹2,999</span><span>↺ &nbsp; Easy 14-day
                returns</span><span>◇ &nbsp; Thoughtfully selected styles</span><span>✓ &nbsp; Secure checkout</span>
        </div>
        <section class="container-wide section">
            <div class="section-title">
                <div><span class="eyebrow">FIND YOUR EVERYDAY</span>
                    <h2>Style, your way.</h2>
                </div><a class="text-link" href="collections.html">All collections ↗</a>
            </div>
            <div class="category-grid"><a class="category-tile" href="shop.html?category=Women"><img class=""
                        src="https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?auto=format&amp;fit=crop&amp;w=900&amp;q=85"
                        onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-7.svg') }}'" alt="The Sunday Dress"
                        loading="lazy">
                    <div>
                        <h3>Women</h3><span>Explore collection ↗</span>
                    </div>
                </a><a class="category-tile" href="shop.html?category=Men"><img class=""
                        src="https://images.unsplash.com/photo-1598033129183-c4f50c736f10?auto=format&amp;fit=crop&amp;w=900&amp;q=85"
                        onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-6.svg') }}'" alt="Everyday Overshirt"
                        loading="lazy">
                    <div>
                        <h3>Men</h3><span>Explore collection ↗</span>
                    </div>
                </a><a class="category-tile" href="shop.html?category=Accessories"><img class=""
                        src="https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&amp;fit=crop&amp;w=900&amp;q=85"
                        onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-10.svg') }}'" alt="Everyday Tote"
                        loading="lazy">
                    <div>
                        <h3>Accessories</h3><span>Explore collection ↗</span>
                    </div>
                </a></div>
        </section>
        <section class="container-wide section">
            <div class="section-title">
                <div><span class="eyebrow">ON REPEAT, FOR A REASON</span>
                    <h2>The everyday favourites.</h2>
                </div><a class="text-link" href="shop.html">Shop all pieces ↗</a>
            </div>
            <div class="product-grid">
                <article class="product-card">
                    <div class="product-photo"><a href="product.html?id=1"><img class=""
                                src="https://images.unsplash.com/photo-1598554747436-c9293d6a588f?auto=format&amp;fit=crop&amp;w=900&amp;q=85"
                                onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-1.svg') }}'"
                                alt="The Everyday Shirt" loading="lazy"></a><span
                            class="tag">Bestseller</span><button class="wish" data-wish="1"
                            aria-label="Save The Everyday Shirt"><svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 00-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 000-7.8Z" />
                            </svg></button><a class="quick" href="product.html?id=1">Choose your size ↗</a></div>
                    <div class="product-meta">
                        <div><span class="micro muted">Women / Shirts</span>
                            <h3><a href="product.html?id=1">The Everyday Shirt</a></h3>
                        </div><span class="swatch" style="background:#cdbba4"></span>
                    </div><span>₹1,899</span> <del>₹2,499</del>
                </article>
                <article class="product-card">
                    <div class="product-photo"><a href="product.html?id=2"><img class=""
                                src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&amp;fit=crop&amp;w=900&amp;q=85"
                                onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-2.svg') }}'"
                                alt="Relaxed Cotton Tee" loading="lazy"></a><span
                            class="tag">Essential</span><button class="wish" data-wish="2"
                            aria-label="Save Relaxed Cotton Tee"><svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 00-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 000-7.8Z" />
                            </svg></button><a class="quick" href="product.html?id=2">Choose your size ↗</a></div>
                    <div class="product-meta">
                        <div><span class="micro muted">Men / T-shirts</span>
                            <h3><a href="product.html?id=2">Relaxed Cotton Tee</a></h3>
                        </div><span class="swatch" style="background:#e8e4db"></span>
                    </div><span>₹899</span> <del>₹1,199</del>
                </article>
                <article class="product-card">
                    <div class="product-photo"><a href="product.html?id=3"><img class=""
                                src="https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&amp;fit=crop&amp;w=900&amp;q=85"
                                onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-3.svg') }}'"
                                alt="The Weekend Blazer" loading="lazy"></a><span class="tag">New</span><button
                            class="wish" data-wish="3" aria-label="Save The Weekend Blazer"><svg
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 00-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 000-7.8Z" />
                            </svg></button><a class="quick" href="product.html?id=3">Choose your size ↗</a></div>
                    <div class="product-meta">
                        <div><span class="micro muted">Women / Outerwear</span>
                            <h3><a href="product.html?id=3">The Weekend Blazer</a></h3>
                        </div><span class="swatch" style="background:#a68b6a"></span>
                    </div><span>₹3,499</span> <del>₹4,499</del>
                </article>
                <article class="product-card">
                    <div class="product-photo"><a href="product.html?id=4"><img class=""
                                src="https://images.unsplash.com/photo-1542272604-787c3835535d?auto=format&amp;fit=crop&amp;w=900&amp;q=85"
                                onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-4.svg') }}'"
                                alt="Straight Leg Denim" loading="lazy"></a><span
                            class="tag">Bestseller</span><button class="wish" data-wish="4"
                            aria-label="Save Straight Leg Denim"><svg viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 00-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 000-7.8Z" />
                            </svg></button><a class="quick" href="product.html?id=4">Choose your size ↗</a></div>
                    <div class="product-meta">
                        <div><span class="micro muted">Men / Denim</span>
                            <h3><a href="product.html?id=4">Straight Leg Denim</a></h3>
                        </div><span class="swatch" style="background:#66809a"></span>
                    </div><span>₹2,299</span> <del>₹2,799</del>
                </article>
            </div>
        </section>
        <section class="editorial">
            <div><img class=""
                    src="https://images.unsplash.com/photo-1434389677669-e08b4cac3105?auto=format&amp;fit=crop&amp;w=900&amp;q=85"
                    onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-5.svg') }}'" alt="Soft Knit Cardigan"
                    loading="lazy"></div>
            <div class="editorial-copy"><span class="eyebrow">THE SLOW SUNDAY EDIT</span>
                <h2>Less effort.<br>More <em>you.</em></h2>
                <p>Soft textures. Easy silhouettes. Pieces that take you from the first coffee to the last light.</p><a
                    class="btn btn-dark" href="shop.html?category=Women">Discover the edit ↗</a>
            </div>
        </section>
        <section class="container-wide section">
            <div class="section-title">
                <div><span class="eyebrow">FRESH PERSPECTIVES</span>
                    <h2>Notes on everyday style.</h2>
                </div><a class="text-link" href="journal.html">Visit the journal ↗</a>
            </div>
            <div class="story-grid"><a class="story-card" href="article.html?story=1"><img class=""
                        src="https://images.unsplash.com/photo-1598554747436-c9293d6a588f?auto=format&amp;fit=crop&amp;w=900&amp;q=85"
                        onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-1.svg') }}'" alt="The Everyday Shirt"
                        loading="lazy"><span class="eyebrow">STYLE NOTES / 01</span>
                    <h3>A wardrobe that works with you.</h3><span class="text-link">Read the story ↗</span>
                </a><a class="story-card" href="article.html?story=2"><img class=""
                        src="https://images.unsplash.com/photo-1434389677669-e08b4cac3105?auto=format&amp;fit=crop&amp;w=900&amp;q=85"
                        onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-5.svg') }}'" alt="Soft Knit Cardigan"
                        loading="lazy"><span class="eyebrow">STYLE NOTES / 02</span>
                    <h3>The texture of a slower weekend.</h3><span class="text-link">Read the story ↗</span>
                </a><a class="story-card" href="article.html?story=3"><img class=""
                        src="https://images.unsplash.com/photo-1542272604-787c3835535d?auto=format&amp;fit=crop&amp;w=900&amp;q=85"
                        onerror="this.onerror=null;this.src='{{ asset('frontend/assets/images/product-4.svg') }}'" alt="Straight Leg Denim"
                        loading="lazy"><span class="eyebrow">STYLE NOTES / 03</span>
                    <h3>Your denim, three different ways.</h3><span class="text-link">Read the story ↗</span>
                </a></div>
        </section>
@endsection
