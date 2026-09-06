<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Drop line rows the user added but never filled in, so an empty trailing
     * row is not a validation error.
     */
    protected function prepareForValidation(): void
    {
        $lines = collect($this->input('lines', []))
            ->reject(fn ($line) => blank($line['item_id'] ?? null)
                && blank($line['description'] ?? null)
                && (float) ($line['qty'] ?? 0) * (float) ($line['unit_price'] ?? 0) === 0.0)
            ->values()
            ->all();

        $this->merge(['lines' => $lines]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'party_id' => ['required', 'integer', 'exists:parties,id'],
            'doc_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:doc_date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'external_ref' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['nullable', 'integer', 'exists:items,id'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.qty' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'lines.*.description' => 'line description',
            'lines.*.qty' => 'quantity',
            'lines.*.unit_price' => 'unit price',
        ];
    }
}
