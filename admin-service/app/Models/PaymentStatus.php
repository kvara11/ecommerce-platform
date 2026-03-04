<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentStatus extends Model
{
    protected $table = 'payment_statuses';

    protected $fillable = [
        'name',
        'label',
    ];

    public const STATUSES = [
        1 => 'pending',
        2 => 'paid',
        3 => 'failed',
        4 => 'refunded',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'payment_status_id', 'id');
    }   
}
