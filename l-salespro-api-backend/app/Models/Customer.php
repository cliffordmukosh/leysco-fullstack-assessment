<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'type', 'category', 'contact_person', 'phone', 'email', 'tax_id',
        'payment_terms', 'credit_limit', 'current_balance', 'latitude', 'longitude',
        'address', 'territory',
    ];

    protected $casts = [
        'payment_terms' => 'integer',
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Customer has many Orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}