<?php

namespace App\Models;

use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'doc_type',
        'number',
        'party_id',
        'doc_date',
        'due_date',
        'status',
        'subtotal',
        'discount',
        'tax_amount',
        'total',
        'settled_total',
        'external_ref',
        'notes',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'doc_date' => 'date',
            'due_date' => 'date',
            'posted_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'settled_total' => 'decimal:2',
        ];
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSales(): bool
    {
        return $this->doc_type === 'sales_invoice';
    }

    public function isPurchase(): bool
    {
        return $this->doc_type === 'purchase_invoice';
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DocumentLine::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Payment::class,
            PaymentAllocation::class,
            'document_id',
            'id',
            'id',
            'payment_id',
        );
    }
}
