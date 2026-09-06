<?php

namespace App\Modules\Clothing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'item_variant_id',
        'direction',
        'qty',
        'unit_cost',
        'reference_type',
        'reference_id',
        'moved_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'moved_at' => 'datetime',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class, 'item_variant_id');
    }
}
