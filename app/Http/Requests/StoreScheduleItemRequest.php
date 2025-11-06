<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleItemRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'time' => 'nullable|date_format:H:i',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'location' => 'nullable|string|max:255',
            'transport_type' => 'nullable|string|in:train,bus,plane,car,walk,other',
            'transport_from' => 'nullable|string|max:255',
            'transport_to' => 'nullable|string|max:255',
            'transport_duration' => 'nullable|integer|min:0',
            'transport_cost' => 'nullable|integer|min:0',
            'order' => 'required|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'タイトルは必須です。',
            'time.date_format' => '時刻は HH:MM 形式で入力してください。',
            'transport_type.in' => '移動手段は指定された値から選択してください。',
        ];
    }
}
