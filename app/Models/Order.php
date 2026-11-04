<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'customer_name',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'landmark',
        'subtotal',
        'delivery_fee',
        'total_amount',
        'payment_method',
        'payment_id',
        'payment_status',
        'order_status',

        // Stripe
        'stripe_session_id',
        'stripe_payment_id',

        // Razorpay
        'razorpay_order_id',
        'razorpay_payment_id',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the primary transaction or reference ID according to payment method.
     */
    public function getPaymentReferenceIdAttribute(): ?string
    {
        if (strtolower((string) $this->payment_method) === 'razorpay') {
            return $this->razorpay_payment_id ?: $this->payment_id ?: $this->razorpay_order_id;
        }

        if (strtolower((string) $this->payment_method) === 'stripe') {
            return $this->stripe_payment_id ?: $this->payment_id ?: $this->stripe_session_id;
        }

        return $this->payment_id ?: $this->razorpay_payment_id ?: $this->stripe_payment_id;
    }

    /**
     * Get the Razorpay payment or order ID.
     */
    public function getRazorpayIdAttribute(): ?string
    {
        return $this->razorpay_payment_id ?: $this->razorpay_order_id ?: (strtolower((string) $this->payment_method) === 'razorpay' ? $this->payment_id : null);
    }

    /**
     * Get formatted payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match (strtolower((string) $this->payment_method)) {
            'razorpay' => 'Razorpay (Online)',
            'stripe' => 'Stripe (Card)',
            'cod' => 'Cash on Delivery (COD)',
            default => strtoupper((string) ($this->payment_method ?: 'N/A')),
        };
    }
}
