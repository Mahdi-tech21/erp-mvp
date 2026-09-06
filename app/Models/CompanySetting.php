<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'currency',
        'tax_rate',
        'sales_prefix',
        'purchase_prefix',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
        ];
    }

    /**
     * The single settings row.
     */
    public static function current(): self
    {
        return static::query()->firstOrFail();
    }
}
