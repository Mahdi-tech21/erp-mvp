<?php

namespace App\Services;

use App\Events\DocumentPosted;
use App\Events\DocumentVoided;
use App\Exceptions\DomainException;
use App\Models\CompanySetting;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

/**
 * The one engine for both sales invoices and purchase invoices. Where a rule
 * differs by direction it branches on doc_type here, once - the service is
 * never forked.
 */
class DocumentService
{
    public function __construct(private NumberGenerator $numbers) {}

    /**
     * Freeze a draft: recompute totals, assign the number, fire DocumentPosted.
     */
    public function post(Document $document): Document
    {
        if ($document->status !== 'draft') {
            throw new DomainException('Only a draft document can be posted.');
        }

        $document->loadMissing('lines', 'party');

        if ($document->lines->isEmpty()) {
            throw new DomainException('A document needs at least one line before it can be posted.');
        }

        $this->assertPartyMatchesType($document);

        return DB::transaction(function () use ($document) {
            $this->recalculateTotals($document);

            $document->forceFill([
                'number' => $this->numbers->next($document->doc_type),
                'status' => 'posted',
                'posted_at' => now(),
            ])->save();

            DocumentPosted::dispatch($document);

            return $document;
        });
    }

    /**
     * Recompute a draft's line totals, subtotal, tax and total from its
     * current lines and header discount. Used when a draft is saved and again
     * inside post(). Does not touch status or number.
     */
    public function recalculateTotals(Document $document): Document
    {
        $document->loadMissing('lines');

        $subtotal = $this->recomputeLines($document);
        $discount = round((float) $document->discount, 2);

        if ($discount > $subtotal) {
            throw new DomainException('The discount cannot be more than the subtotal.');
        }

        $rate = (float) CompanySetting::current()->tax_rate;
        $taxAmount = round(($subtotal - $discount) * $rate / 100, 2);

        $document->forceFill([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_amount' => $taxAmount,
            'total' => round($subtotal - $discount + $taxAmount, 2),
        ])->save();

        return $document;
    }

    /**
     * Void a posted document. Fires DocumentVoided *before* the flip so a
     * listener can veto by throwing. Never deletes; keeps the number.
     */
    public function void(Document $document): Document
    {
        if (! in_array($document->status, ['posted', 'partial', 'settled'], true)) {
            throw new DomainException('Only a posted document can be voided.');
        }

        if ($document->allocations()->exists()) {
            throw new DomainException('This document has payments allocated to it - unallocate them first.');
        }

        return DB::transaction(function () use ($document) {
            DocumentVoided::dispatch($document);

            $document->forceFill(['status' => 'void'])->save();

            return $document;
        });
    }

    private function assertPartyMatchesType(Document $document): void
    {
        $party = $document->party;

        if ($document->doc_type === 'sales_invoice' && ! $party->is_customer) {
            throw new DomainException('A sales invoice must be for a customer.');
        }

        if ($document->doc_type === 'purchase_invoice' && ! $party->is_supplier) {
            throw new DomainException('A purchase invoice must be for a supplier.');
        }
    }

    /**
     * Recompute every line_total from qty x unit_price and return the subtotal.
     */
    private function recomputeLines(Document $document): float
    {
        $subtotal = 0.0;

        foreach ($document->lines as $line) {
            $lineTotal = round((float) $line->qty * (float) $line->unit_price, 2);

            if ((float) $line->line_total !== $lineTotal) {
                $line->forceFill(['line_total' => $lineTotal])->save();
            }

            $subtotal += $lineTotal;
        }

        return round($subtotal, 2);
    }
}
