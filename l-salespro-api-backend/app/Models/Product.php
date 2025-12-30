<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'category_id', 'subcategory', 'description',
        'price', 'tax_rate', 'unit', 'packaging', 'min_order_quantity', 'reorder_level',
    ];

    /**
     * Product belongs to one Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Product has many Inventory records (across warehouses)
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     *  Accessor for total stock across warehouses
     */
    public function getTotalStockAttribute(): int
    {
        return $this->inventory->sum('quantity');
    }
}