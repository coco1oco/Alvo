<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
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
            'name'    => [
                'required',
                'string',
                'max:100',
                Rule::unique('accounts')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                })
            ],
            'type'    => 'required|in:cash,bank,credit_card,savings,other',
            'balance' => 'nullable|numeric',
            'color'   => 'nullable|string|max:7',
            'icon'    => 'nullable|string|max:50',
        ];
    }
}
