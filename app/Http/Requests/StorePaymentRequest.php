<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Drop allocation rows left at zero so an untouched open-document row is
     * not a validation error.
     */
    protected function prepareForValidation(): void
    {
        $allocations = collect($this->input('allocations', []))
            ->filter(fn ($row) => (float) ($row['amount'] ?? 0) > 0)
            ->values()
            ->all();

        $this->merge(['allocations' => $allocations]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'party_id' => ['required', 'integer', 'exists:parties,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['required', Rule::in(config('payments.methods'))],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'allocations' => ['nullable', 'array'],
            'allocations.*.document_id' => ['required', 'integer', 'exists:documents,id'],
            'allocations.*.amount' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
