<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
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
        $account = $this->route('account');
        return [
            'name'  => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('accounts')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                })->ignore($account->id)
            ],
            'type'  => 'sometimes|required|in:cash,bank,credit_card,savings,other',
            'color' => 'sometimes|nullable|string|max:7',
            'icon'  => 'sometimes|nullable|string|max:50',
            'is_archived' => 'sometimes|boolean',
        ];
    }
}
