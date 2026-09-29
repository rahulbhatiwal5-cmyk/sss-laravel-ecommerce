<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;
     protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'material',
        'care_instructions',
        'gender',
        'price',
        'sale_price',
        'is_featured',
        'is_active',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Limit a query to products that may be shown on the storefront.
     */
    public function scopePubliclyVisible(Builder $query, ?DateTimeInterface $now = null): Builder
    {
        $now ??= now();

        return $query
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('is_active', true));
    }

    /**
     * Check the same visibility rules for an already-loaded storefront product.
     */
    public function isPubliclyVisible(?DateTimeInterface $now = null): bool
    {
        $now ??= now();

        return $this->is_active
            && $this->published_at !== null
            && $this->published_at->lte($now)
            && $this->category?->is_active === true;
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)
            ->where('is_primary', true);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function registerMediaCollections(): void
{
    $this
        ->addMediaCollection('main_image')
        ->singleFile();

    $this->addMediaCollection('gallery');
}

 public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->fit(Fit::Crop, 300, 400);

        $this
            ->addMediaConversion('medium')
            ->fit(Fit::Contain, 600, 800);
    }
    
}
