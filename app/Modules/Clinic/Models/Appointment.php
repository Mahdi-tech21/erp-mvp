<?php

namespace App\Modules\Clinic\Models;

use App\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_name',
        'starts_at',
        'duration_minutes',
        'service_item_id',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime'];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function serviceItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'service_item_id');
    }

    public function canBeInvoiced(): bool
    {
        return $this->status !== 'invoiced' && $this->service_item_id !== null;
    }
}
