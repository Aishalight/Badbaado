<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReferralRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hospital_id !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'receiving_hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'department' => ['required', 'string', 'max:255'],
            'referral_reason' => ['required', 'string', 'max:1000'],
            'symptoms' => ['nullable', 'string', 'max:2000'],
            'vitals' => ['nullable', 'array'],
            'vitals.bp' => ['nullable', 'string', 'max:50'],
            'vitals.hr' => ['nullable', 'numeric'],
            'vitals.rr' => ['nullable', 'numeric'],
            'vitals.spo2' => ['nullable', 'numeric', 'between:0,100'],
            'vitals.temp' => ['nullable', 'numeric'],
            'consciousness' => ['nullable', 'string', 'max:255'],
            'trauma_indicator' => ['sometimes', 'boolean'],
            'existing_conditions' => ['nullable', 'string', 'max:2000'],
            'current_interventions' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'urgency' => ['nullable', Rule::in(['routine', 'urgent', 'emergent', 'critical'])],
            'is_emergency' => ['sometimes', 'boolean'],
            'patient.name' => ['required', 'string', 'max:255'],
            'patient.age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'patient.gender' => ['nullable', 'string', 'max:50'],
            'patient.blood_group' => ['nullable', 'string', 'max:10'],
            'attachments' => ['sometimes', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'max:10240',
                'mimes:pdf,jpeg,jpg,png,doc,docx,xls,xlsx,csv,txt',
            ],
        ];
    }
}
