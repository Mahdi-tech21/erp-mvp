<?php

namespace App\Modules\Clinic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
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
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'service_item_id' => ['nullable', 'integer', 'exists:items,id'],
            'status' => ['required', Rule::in(['scheduled', 'done', 'cancelled', 'invoiced'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
