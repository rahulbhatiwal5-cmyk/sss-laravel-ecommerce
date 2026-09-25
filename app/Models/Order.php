<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'coupon_id',

        'customer_name',
        'customer_email',
        'customer_phone',

        'shipping_address',
        'shipping_address_2',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',

        'subtotal',
        'discount_amount',
        'shipping_amount',
        'tax_amount',
        'total_amount',

        'payment_method',
        'payment_status',
        'order_status',

        'customer_note',
        'admin_note',

        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',

            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function returns()
    {
        return $this->hasMany(ProductReturn::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}