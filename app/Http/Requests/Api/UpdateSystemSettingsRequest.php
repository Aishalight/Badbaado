<?php

namespace App\Http\Requests\Api;

use App\Models\SystemSetting;
use App\Services\SettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class UpdateSystemSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::forUser($this->user())->allows('update', SystemSetting::make());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable'],
        ];
    }

    /**
     * Validate each submitted value against the type of its definition so the
     * guard between string/bool/int settings lives in the backend.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $service = app(SettingsService::class);

            foreach ($this->input('settings', []) as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                $definition = $service->definition((string) $key);

                if ($definition === null) {
                    $validator->errors()->add("settings.{$key}", "Unknown setting [{$key}].");

                    continue;
                }

                $type = $definition['type'];

                if ($value !== true && $value !== false) {
                    $boolean = in_array($type, ['boolean'], true);
                    $integer = in_array($type, ['integer'], true);
                    $string = in_array($type, ['string'], true);

                    if ($boolean && ! in_array($value, ['0', '1', 0, 1], true)) {
                        $validator->errors()->add("settings.{$key}", 'Must be a boolean.');
                    }
                    if ($integer && filter_var($value, FILTER_VALIDATE_INT) === false) {
                        $validator->errors()->add("settings.{$key}", 'Must be an integer.');
                    }
                    if ($string && ! is_string($value)) {
                        $validator->errors()->add("settings.{$key}", 'Must be a string.');
                    }
                }
            }
        });
    }
}
