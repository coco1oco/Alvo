<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'  => 'sometimes|required|string|max:100',
            'type'  => 'sometimes|required|in:income,expense',
            'color' => 'sometimes|nullable|string|max:7',
            'icon'  => 'sometimes|nullable|string|max:50',
        ];
    }
}
