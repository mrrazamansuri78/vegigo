<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'farm_name',
        'location_city',
        'location_state',
        'farm_size_acres',
        'primary_crop',
        'storage',
        'certifications',
        'fulfillment_rate',
        'average_rating',
        'repeat_partners',
    ];

    protected $casts = [
        'farm_size_acres' => 'float',
        'fulfillment_rate' => 'float',
        'average_rating' => 'float',
        'repeat_partners' => 'integer',
    ];

    public function farmer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


