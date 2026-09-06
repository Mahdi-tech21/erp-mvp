<?php

namespace App\Http\Controllers;

use App\Exceptions\DomainException;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;

/**
 * Post and void, for both document types. The redirect target comes from
 * the document's own type config, so one controller serves both.
 */
class DocumentActionController extends Controller
{
    public function __construct(private DocumentService $documents) {}

    public function post(Document $document): RedirectResponse
    {
        try {
            $this->documents->post($document);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route($this->routePrefix($document).'.show', $document)
            ->with('status', 'Posted as '.$document->number.'.');
    }

    public function void(Document $document): RedirectResponse
    {
        try {
            $this->documents->void($document);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $document->number.' voided.');
    }

    private function routePrefix(Document $document): string
    {
        return config("documents.types.{$document->doc_type}.route");
    }
}
