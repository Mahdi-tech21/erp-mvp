<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'cost_price' => $this->filled('cost_price') ? $this->input('cost_price') : 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $itemId = $this->route('item')?->id;

        return [
            'sku' => ['required', 'string', 'max:64', Rule::unique('items', 'sku')->ignore($itemId)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['product', 'service'])],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'cost_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'is_active' => ['boolean'],
        ];
    }
}
