<?php

namespace App\Modules\Clinic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Modules\Clinic\Models\Appointment;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * The demo moment: an appointment becomes a draft sales invoice through the
 * core engine, no core changes. Same DocumentService, different front door.
 */
class AppointmentInvoiceController extends Controller
{
    public function __invoke(Appointment $appointment, DocumentService $documents): RedirectResponse
    {
        if (! $appointment->canBeInvoiced()) {
            return back()->with('error', 'This appointment has no service set, or has already been invoiced.');
        }

        $appointment->loadMissing('patient', 'serviceItem');

        $document = DB::transaction(function () use ($appointment, $documents) {
            $item = $appointment->serviceItem;

            $document = Document::create([
                'doc_type' => 'sales_invoice',
                'party_id' => $appointment->patient->party_id,
                'doc_date' => now()->toDateString(),
                'status' => 'draft',
            ]);

            $document->lines()->create([
                'item_id' => $item->id,
                'description' => $item->name.' — '.$appointment->doctor_name,
                'qty' => 1,
                'unit_price' => $item->unit_price,
                'line_total' => $item->unit_price,
                'meta' => ['appointment_id' => $appointment->id],
                'sort_order' => 0,
            ]);

            $documents->recalculateTotals($document->load('lines'));
            $appointment->update(['status' => 'invoiced']);

            return $document;
        });

        return redirect()
            ->route('sales-invoices.show', $document)
            ->with('status', 'Draft invoice created from the appointment.');
    }
}
