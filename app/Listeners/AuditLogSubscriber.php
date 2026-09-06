<?php

namespace App\Listeners;

use App\Events\DocumentPosted;
use App\Events\DocumentVoided;
use App\Events\PaymentRecorded;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Payment;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Auth;

/**
 * Writes an audit_logs row for every accounting-significant event. Runs
 * synchronously inside the service transaction, so an action and its audit
 * record commit or roll back together.
 */
class AuditLogSubscriber
{
    /**
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            DocumentPosted::class => 'onDocumentPosted',
            DocumentVoided::class => 'onDocumentVoided',
            PaymentRecorded::class => 'onPaymentRecorded',
        ];
    }

    public function onDocumentPosted(DocumentPosted $event): void
    {
        $document = $event->document->loadMissing('party');

        $this->write('document.posted', $document, sprintf(
            'Posted %s for %s (%s)',
            $document->number,
            $document->party->name,
            number_format((float) $document->total, 2),
        ), [
            'number' => $document->number,
            'doc_type' => $document->doc_type,
            'total' => (string) $document->total,
            'party_id' => $document->party_id,
        ]);
    }

    public function onDocumentVoided(DocumentVoided $event): void
    {
        $document = $event->document->loadMissing('party');

        $this->write('document.voided', $document, sprintf(
            'Voided %s for %s',
            $document->number,
            $document->party->name,
        ), [
            'number' => $document->number,
            'doc_type' => $document->doc_type,
            'total' => (string) $document->total,
            'party_id' => $document->party_id,
        ]);
    }

    public function onPaymentRecorded(PaymentRecorded $event): void
    {
        $payment = $event->payment->loadMissing('party', 'allocations');

        $this->write('payment.recorded', $payment, sprintf(
            'Recorded %s payment %s (%s) for %s',
            $payment->direction,
            number_format((float) $payment->amount, 2),
            $payment->method,
            $payment->party->name,
        ), [
            'direction' => $payment->direction,
            'amount' => (string) $payment->amount,
            'method' => $payment->method,
            'party_id' => $payment->party_id,
            'allocations' => $payment->allocations->count(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function write(string $action, Document|Payment $subject, string $summary, array $properties): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $subject->getMorphClass(),
            'auditable_id' => $subject->getKey(),
            'summary' => $summary,
            'properties' => $properties,
        ]);
    }
}
