<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cover_image' => 'nullable|string|max:255',
            'is_public' => 'boolean',
            'memo' => 'nullable|string|max:10000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'タイトルは必須です。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'start_date.required' => '開始日は必須です。',
            'start_date.date' => '開始日は有効な日付である必要があります。',
            'end_date.required' => '終了日は必須です。',
            'end_date.date' => '終了日は有効な日付である必要があります。',
            'end_date.after_or_equal' => '終了日は開始日以降である必要があります。',
        ];
    }
}
