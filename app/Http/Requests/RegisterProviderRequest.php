<?php

namespace App\Http\Requests;

use App\Enums\ProviderApplicationType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterProviderRequest extends FormRequest
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
        $doctor = $this->input('type') === ProviderApplicationType::DOCTOR->value;

        return [
            'type' => ['required', Rule::enum(ProviderApplicationType::class)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:50'],

            'title' => ['nullable', 'string', 'max:255'],
            'specialty_id' => [$doctor ? 'required' : 'prohibited', 'integer', 'exists:specialties,id'],
            'license_number' => [$doctor ? 'required' : 'prohibited', 'string', 'max:100'],

            'facility_name' => [$doctor ? 'prohibited' : 'required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', Rule::in(['primary', 'secondary', 'tertiary'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'specialty_id.required' => 'Choose the specialty you practise in.',
            'specialty_id.prohibited' => 'The specialty only applies to individual doctor registrations.',
            'license_number.required' => 'Enter your medical registration number.',
            'license_number.prohibited' => 'The registration number only applies to individual doctor registrations.',
            'facility_name.prohibited' => 'The facility name only applies to hospital registrations.',
        ];
    }

    /**
     * Normalise the validated payload for the application record.
     *
     * @return array<string, mixed>
     */
    public function applicationPayload(): array
    {
        $payload = $this->validated();

        unset($payload['type'], $payload['password_confirmation']);

        return $payload;
    }
}
