<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'product_id',
        'quantity',
        'unit',
        'status',
        'admin_note',
    ];

    public function farmer()
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
