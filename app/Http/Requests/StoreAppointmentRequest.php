<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_phone' => ['required', 'string', 'max:30'],
            'service' => ['required', 'string', Rule::in(array_keys(config('spa.services')))],
            'therapist_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::Therapist->value),
            ],
            'appointment_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
