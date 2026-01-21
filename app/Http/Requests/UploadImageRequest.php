<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
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
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB
            'imageable_type' => 'required|string',
            'imageable_id' => 'required|integer',
            'caption' => 'nullable|string|max:500',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert short type names to full class names
        $typeMap = [
            'plan' => 'App\\Models\\Plan',
            'schedule_item' => 'App\\Models\\ScheduleItem',
        ];

        if ($this->has('imageable_type') && isset($typeMap[$this->imageable_type])) {
            $this->merge([
                'imageable_type' => $typeMap[$this->imageable_type],
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'image.required' => '画像ファイルは必須です。',
            'image.image' => 'アップロードされたファイルは画像である必要があります。',
            'image.mimes' => '画像はJPEG、PNG形式である必要があります。',
            'image.max' => '画像のサイズは5MB以下である必要があります。',
        ];
    }
}
