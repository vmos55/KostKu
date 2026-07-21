<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePreferencesRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'locale' => ['required', Rule::in(['id', 'en'])],
            'timezone' => ['required', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])],
            'email_notifications' => ['required', 'boolean'],
            'booking_notifications' => ['required', 'boolean'],
            'payment_notifications' => ['required', 'boolean'],
        ];
    }
}
