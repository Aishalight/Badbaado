<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'title' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role_slug' => ['required', Rule::in($allowedRoles)],
            'hospital_id' => ['nullable', 'integer', 'exists:hospitals,id'],
        ];
    }
}
