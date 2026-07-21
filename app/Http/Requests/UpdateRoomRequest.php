<?php

namespace App\Http\Requests;

use App\Enums\RoomStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
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
            'kost_id' => ['required', 'exists:kosts,id'],
            'room_number' => [
                'required', 'string', 'max:30',
                Rule::unique('rooms')->where(fn ($query) => $query->where('kost_id', $this->integer('kost_id')))->ignore($this->route('room')),
            ],
            'floor' => ['nullable', 'integer', 'min:1', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(RoomStatus::class)],
        ];
    }
}
