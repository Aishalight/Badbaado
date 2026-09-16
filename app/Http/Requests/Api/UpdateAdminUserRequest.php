<?php

namespace App\Http\Requests\Api;

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
        $allowedRoles = ['healthcare_worker', 'referral_coordinator', 'hospital_admin'];
        if ($this->user()?->hasRole('system_admin')) {
            $allowedRoles[] = 'system_admin';
        }

        return [
            'role_slug' => ['sometimes', Rule::in($allowedRoles)],
            'hospital_id' => ['sometimes', 'nullable', 'integer', 'exists:hospitals,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
