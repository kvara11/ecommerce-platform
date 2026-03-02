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
        0 => 'pending',
        1 => 'paid',
        2 => 'failed',
        3 => 'refunded',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'payment_status_id', 'id');
    }   
}
