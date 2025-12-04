<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerNotification extends Model
{
    use HasFactory;

    protected $table = 'farmer_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function farmer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


