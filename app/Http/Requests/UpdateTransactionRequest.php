<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
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
            'account_id'    => 'sometimes|required|exists:accounts,id',
            'to_account_id' => 'nullable|exists:accounts,id',
            'category_id'   => 'nullable|exists:categories,id',
            'type'          => 'sometimes|required|in:income,expense,transfer',
            'amount'        => 'sometimes|required|numeric|min:0.01',
            'description'   => 'nullable|string|max:500',
            'date'          => 'sometimes|required|date',
        ];
    }
}
