<?php

namespace App\Events;

use App\Models\Document;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired while the document is still `posted`, before the status flips to
 * `void`. A listener may throw to veto the void (the clothing listener does
 * this when reversing stock would drive a variant negative).
 */
class DocumentVoided
{
    use Dispatchable, SerializesModels;

    public function __construct(public Document $document) {}
}
