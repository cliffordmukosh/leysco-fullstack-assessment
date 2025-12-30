<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'address', 'manager_email', 'phone', 'capacity', 'latitude', 'longitude',
    ];

    /**
     * Warehouse has many Inventory records
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Warehouse has many StockTransfers as from/to
     */
    public function transfersFrom(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }

    /**
     * Accessor for current capacity usage
     */
    public function getCurrentUsageAttribute(): int
    {
        return $this->inventory->sum('quantity');
    }

    /**
     * Check if has capacity for additional quantity
     */
    public function hasCapacity(int $quantity): bool
    {
        return ($this->current_usage + $quantity) <= $this->capacity;
    }
}