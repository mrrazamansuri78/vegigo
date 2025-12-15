<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'delivery_boy_id',
        'order_code',
        'customer_name',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'drop_address',
        'drop_latitude',
        'drop_longitude',
        'landmark',
        'drop_contact_person',
        'drop_contact_phone',
        'distance_km',
        'total_amount',
        'items',
        'status',
        'ready_date',
        'accepted_at',
        'picked_up_at',
        'delivered_at',
    ];

    protected $casts = [
        'items' => 'array',
        'ready_date' => 'date',
        'accepted_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function farmer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deliveryBoy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }
}


