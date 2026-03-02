<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatus extends Model
{
    protected $table = 'order_statuses';
    
    protected $fillable = [
        'name',
        'label',
    ];

    public const STATUSES = [
        0 => 'pending',
        1 => 'processing',
        2 => 'confirmed',
        3 => 'shipped',
        4 => 'delivered',
        5 => 'cancelled',
        6 => 'refunded',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }   
}
