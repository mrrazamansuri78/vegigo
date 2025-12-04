<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'quantity',
        'price_per_unit',
        'unit',
        'is_organic',
        'image_path',
        'status',
    ];

    protected $casts = [
        'quantity' => 'float',
        'price_per_unit' => 'float',
        'is_organic' => 'boolean',
    ];

    public function farmer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


