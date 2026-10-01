<?php

namespace App\Http\Requests\Api;

use App\Enums\Urgency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
            'receiving_hospital_id' => [
                'required',
                'integer',
                Rule::exists('hospitals', 'id')->where(fn (QueryBuilder $query) => $query->where('is_active', true)),
            ],
            'intended_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')
                    ->where(fn (QueryBuilder $query) => $query
                        ->where('is_active', true)
                        ->where('hospital_id', $this->input('receiving_hospital_id'))
                        ->whereExists(fn (QueryBuilder $roles) => $roles
                            ->selectRaw(1)
                            ->from('roles')
                            ->whereColumn('roles.id', 'users.role_id')
                            ->whereIn('roles.slug', ['healthcare_worker', 'referral_coordinator']))),
            ],
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
            'urgency' => ['nullable', Rule::enum(Urgency::class)],
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

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'receiving_hospital_id.exists' => 'The selected receiving hospital is not available.',
            'intended_user_id.exists' => 'The selected doctor is not active clinical staff at the receiving hospital.',
        ];
    }
}
