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
        'stripe_session_id',
        'stripe_session_id',
        'stripe_payment_id',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
