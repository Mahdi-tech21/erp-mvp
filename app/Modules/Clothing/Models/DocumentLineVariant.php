<?php

namespace App\Modules\Clothing\Models;

use App\Models\DocumentLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLineVariant extends Model
{
    protected $fillable = ['document_line_id', 'item_variant_id'];

    public function line(): BelongsTo
    {
        return $this->belongsTo(DocumentLine::class, 'document_line_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class, 'item_variant_id');
    }
}
