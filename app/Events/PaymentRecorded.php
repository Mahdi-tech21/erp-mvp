<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A payment has been recorded and its allocations written; affected
 * documents already have their settled_total and status recomputed.
 */
class PaymentRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment) {}
}
