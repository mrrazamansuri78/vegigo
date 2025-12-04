<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_summary',
        'preferred_date',
        'status',
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    public function farmer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


