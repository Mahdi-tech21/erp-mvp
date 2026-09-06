<?php

namespace App\Modules\Clothing\Models;

use App\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemVariant extends Model
{
    protected $fillable = ['item_id', 'size', 'color', 'sku', 'stock_qty', 'reorder_level'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLow(): bool
    {
        return $this->stock_qty <= $this->reorder_level;
    }

    public function label(): string
    {
        return "{$this->size} / {$this->color}";
    }
}
