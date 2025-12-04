<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryBoyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'role_title',
        'is_verified',
        'current_location_city',
        'current_location_area',
        'current_latitude',
        'current_longitude',
        'current_speed_kmh',
        'battery_percentage',
        'is_on_route',
        'shift_start_time',
        'shift_end_time',
        'vehicle_type',
        'preferred_zone',
        'auto_accept_urgent_jobs',
        'share_live_location',
    ];

    protected $casts = [
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'current_speed_kmh' => 'decimal:2',
        'battery_percentage' => 'integer',
        'is_on_route' => 'boolean',
        'is_verified' => 'boolean',
        'auto_accept_urgent_jobs' => 'boolean',
        'share_live_location' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

