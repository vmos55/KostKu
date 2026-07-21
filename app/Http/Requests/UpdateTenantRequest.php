<?php

namespace App\Http\Requests;

use App\Enums\IdentityVerificationStatus;
use App\Enums\TenantStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'identity_number' => ['nullable', 'string', 'max:30', Rule::unique('tenants', 'identity_number')->ignore($this->route('tenant'))],
            'identity_verification_status' => ['required', Rule::enum(IdentityVerificationStatus::class)],
            'check_in' => ['required', 'date'],
            'check_out' => ['nullable', 'date', 'after_or_equal:check_in'],
            'status' => ['required', Rule::enum(TenantStatus::class)],
        ];
    }
}
