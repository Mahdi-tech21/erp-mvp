<?php

namespace App\Modules\Clothing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $variantId = $this->route('variant')?->id;

        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'size' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:50'],
            'sku' => ['required', 'string', 'max:64', Rule::unique('item_variants', 'sku')->ignore($variantId)],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'opening_qty' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
