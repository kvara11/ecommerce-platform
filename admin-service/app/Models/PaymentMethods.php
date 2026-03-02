<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethods extends Model
{
    protected $table = 'payment_methods';

    protected $fillable = [
        'name',
        'label',
        'is_active',
    ];

    public const METHODS = [
        0 => 'cash',
        1 => 'card',
        2 => 'paypal',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'payment_method_id', 'id');
    }   

}
