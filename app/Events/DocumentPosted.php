<?php

namespace App\Events;

use App\Models\Document;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A draft document has just been posted: totals frozen, number assigned.
 * Modules react here (clothing moves stock). Listeners run synchronously.
 */
class DocumentPosted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Document $document) {}
}
