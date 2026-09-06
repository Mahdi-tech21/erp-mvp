<?php

namespace App\Modules\Clinic;

use App\Models\Document;
use App\Models\Item;
use App\Models\Party;
use App\Modules\Clinic\Models\Appointment;
use App\Modules\Clinic\Models\Patient;
use App\Services\DocumentService;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The clinic deployment's own sell side: real patient names, their visits, and
 * the consultation invoices those visits produce — posted through the core
 * DocumentService so A/R aging, the sales report and statements all have data.
 */
class ClinicDemoSeeder extends Seeder
{
    private const DOCTORS = ['Dr. Adeyemi', 'Dr. Novak', 'Dr. Salah'];

    public function __construct(
        private DocumentService $documents,
        private PaymentService $payments,
    ) {}

    public function run(): void
    {
        if (Patient::query()->exists()) {
            return;
        }

        $services = $this->serviceItems();

        $patients = collect(range(1, 12))->map(fn () => Patient::create([
            'party_id' => Party::factory()->customer()->create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
            ])->id,
            'date_of_birth' => fake()->dateTimeBetween('-80 years', '-6 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female']),
        ]));

        foreach ($patients as $patient) {
            foreach (range(1, fake()->numberBetween(1, 2)) as $ignored) {
                $this->makeAppointment($patient, $services, past: true);
            }

            if (fake()->boolean(60)) {
                $this->makeAppointment($patient, $services, past: false);
            }
        }

        $this->billConsultations();
    }

    /**
     * @return Collection<int, Item>
     */
    private function serviceItems(): Collection
    {
        return collect(['General consultation' => 60, 'Follow-up visit' => 35, 'Minor procedure' => 140])
            ->map(fn ($price, $name) => Item::firstOrCreate(
                ['sku' => 'SVC-'.strtoupper(substr(md5($name), 0, 6))],
                ['name' => $name, 'type' => 'service', 'unit_price' => $price, 'cost_price' => 0, 'is_active' => true],
            ))
            ->values();
    }

    /**
     * @param  Collection<int, Item>  $services
     */
    private function makeAppointment(Patient $patient, Collection $services, bool $past): void
    {
        $day = $past
            ? fake()->dateTimeBetween('-8 weeks', '-2 days')
            : fake()->dateTimeBetween('+1 day', '+3 weeks');

        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_name' => fake()->randomElement(self::DOCTORS),
            'starts_at' => Carbon::parse($day)->setTime(fake()->numberBetween(9, 16), fake()->randomElement([0, 30])),
            'duration_minutes' => fake()->randomElement([20, 30, 45]),
            'service_item_id' => $services->random()->id,
            'status' => $past ? 'done' : 'scheduled',
        ]);
    }

    /**
     * Every completed visit becomes a consultation invoice, then most of them
     * get paid — fully or in part, a few left outstanding.
     */
    private function billConsultations(): void
    {
        $visits = Appointment::with('patient', 'serviceItem')
            ->where('status', 'done')
            ->whereNotNull('service_item_id')
            ->get();

        foreach ($visits as $visit) {
            if (fake()->boolean(15)) {
                continue; // not invoiced yet
            }

            $item = $visit->serviceItem;
            $visitDate = $visit->starts_at->toDateString();

            $document = Document::create([
                'doc_type' => 'sales_invoice',
                'party_id' => $visit->patient->party_id,
                'doc_date' => $visitDate,
                'due_date' => $visit->starts_at->copy()->addDays(14)->toDateString(),
                'status' => 'draft',
            ]);

            $document->lines()->create([
                'item_id' => $item->id,
                'description' => $item->name.' — '.$visit->doctor_name,
                'qty' => 1,
                'unit_price' => $item->unit_price,
                'line_total' => $item->unit_price,
                'meta' => ['appointment_id' => $visit->id],
                'sort_order' => 0,
            ]);

            $this->documents->post($document->load('lines', 'party'));
            $visit->update(['status' => 'invoiced']);

            $this->collectPayment($document->fresh(), $visitDate);
        }
    }

    private function collectPayment(Document $document, string $onDate): void
    {
        $roll = fake()->numberBetween(1, 100);

        if ($roll <= 30) {
            return; // still owing
        }

        $amount = $roll <= 75
            ? (float) $document->total
            : round((float) $document->total * 0.5, 2);

        $this->payments->record([
            'direction' => 'in',
            'party_id' => $document->party_id,
            'payment_date' => $onDate,
            'amount' => $amount,
            'method' => fake()->randomElement(['cash', 'card', 'transfer']),
            'reference' => null,
        ], [
            ['document_id' => $document->id, 'amount' => $amount],
        ]);
    }
}
