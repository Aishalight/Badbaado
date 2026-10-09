<?php

namespace App\Http\Requests\Api;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user()?->role?->slug, ['hospital_admin', 'system_admin'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        $allowedRoles = $this->user()?->hasRole('system_admin')
            ? ['hospital_admin']
            : ['healthcare_worker', 'referral_coordinator'];

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'avatar' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'role_slug' => ['sometimes', Rule::in($allowedRoles)],
            'hospital_id' => $this->user()?->hasRole('system_admin')
                ? ['sometimes', 'nullable', 'integer', 'exists:hospitals,id']
                : ['prohibited'],
            'status' => ['sometimes', Rule::enum(UserStatus::class)],
            'is_active' => ['sometimes', 'boolean'],
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
            'hospital_id.prohibited' => 'Only system administrators can move a user to another hospital.',
        ];
    }
}
