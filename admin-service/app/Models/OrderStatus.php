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
        1 => 'pending',
        2 => 'processing',
        3 => 'confirmed',
        4 => 'shipped',
        5 => 'delivered',
        6 => 'cancelled',
        7 => 'refunded',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }   
}
