<?php

namespace App\Http\Requests\Api;

use App\Enums\ProviderApplicationType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'type' => ['required', Rule::enum(ProviderApplicationType::class)],
            'role_slug' => ['prohibited'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:255'],
            'specialty_id' => [
                $this->input('type') === ProviderApplicationType::DOCTOR->value ? 'required' : 'prohibited',
                'integer',
                'exists:specialties,id',
            ],
            'license_number' => [
                $this->input('type') === ProviderApplicationType::DOCTOR->value ? 'required' : 'prohibited',
                'string',
                'max:100',
            ],
            'facility_name' => [
                $this->input('type') === ProviderApplicationType::HOSPITAL->value ? 'required' : 'prohibited',
                'string',
                'max:255',
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', Rule::in(['primary', 'secondary', 'tertiary'])],
        ];
    }

    /**
     * Remove transport-only fields before storing the application payload.
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
