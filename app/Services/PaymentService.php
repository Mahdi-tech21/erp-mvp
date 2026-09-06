<?php

namespace App\Services;

use App\Events\PaymentRecorded;
use App\Exceptions\DomainException;
use App\Models\Document;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * The one engine for money in (from customers) and money out (to suppliers).
 * A payment may carry allocations that settle specific documents.
 */
class PaymentService
{
    /**
     * @param  array<string, mixed>  $attributes  direction, party_id, payment_date, amount, method, reference?, notes?
     * @param  list<array{document_id: int|string, amount: mixed}>  $allocations
     */
    public function record(array $attributes, array $allocations = []): Payment
    {
        return DB::transaction(function () use ($attributes, $allocations) {
            $payment = Payment::create([
                'direction' => $attributes['direction'],
                'party_id' => $attributes['party_id'],
                'payment_date' => $attributes['payment_date'],
                'amount' => round((float) $attributes['amount'], 2),
                'method' => $attributes['method'],
                'reference' => $attributes['reference'] ?? null,
                'notes' => $attributes['notes'] ?? null,
            ]);

            $this->allocate($payment, $allocations);

            PaymentRecorded::dispatch($payment);

            return $payment->load('allocations');
        });
    }

    /**
     * @param  list<array{document_id: int|string, amount: mixed}>  $allocations
     */
    private function allocate(Payment $payment, array $allocations): void
    {
        // Collapse any repeated document rows into a single amount.
        $byDocument = [];

        foreach ($allocations as $row) {
            $id = (int) $row['document_id'];
            $byDocument[$id] = round(($byDocument[$id] ?? 0) + (float) $row['amount'], 2);
        }

        $byDocument = array_filter($byDocument, fn ($amount) => $amount > 0);

        if ($byDocument === []) {
            return;
        }

        if (round(array_sum($byDocument), 2) > round((float) $payment->amount, 2)) {
            throw new DomainException('The allocations add up to more than the payment.');
        }

        $expectedType = $payment->direction === 'in' ? 'sales_invoice' : 'purchase_invoice';

        foreach ($byDocument as $documentId => $amount) {
            $document = Document::query()->lockForUpdate()->find($documentId);

            if ($document === null) {
                throw new DomainException('Allocation targets a document that does not exist.');
            }

            if ($document->doc_type !== $expectedType) {
                throw new DomainException("A {$payment->direction} payment cannot be allocated to a {$document->doc_type}.");
            }

            if ((int) $document->party_id !== (int) $payment->party_id) {
                throw new DomainException('The payment and the document are for different parties.');
            }

            if (! in_array($document->status, ['posted', 'partial', 'settled'], true)) {
                throw new DomainException('Payments can only be allocated to posted documents.');
            }

            $remaining = round((float) $document->total - (float) $document->settled_total, 2);

            if ($amount > $remaining) {
                throw new DomainException('An allocation is more than the document\'s remaining balance.');
            }

            $payment->allocations()->create([
                'document_id' => $document->id,
                'amount' => $amount,
            ]);

            $this->resettle($document, $amount);
        }
    }

    /**
     * Add an allocation to a document's settled_total and move its status.
     */
    private function resettle(Document $document, float $delta): void
    {
        $settled = round((float) $document->settled_total + $delta, 2);

        $status = match (true) {
            $settled >= (float) $document->total => 'settled',
            $settled > 0 => 'partial',
            default => 'posted',
        };

        $document->forceFill([
            'settled_total' => $settled,
            'status' => $status,
        ])->save();
    }
}
