<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => 'sometimes|required|string|max:255',
            'amount'            => 'sometimes|required|numeric|min:0.01',
            'billing_cycle'     => 'sometimes|required|in:weekly,monthly,yearly',
            'next_renewal_date' => 'sometimes|required|date',
            'account_id'        => 'nullable|exists:accounts,id',
            'category_id'       => 'nullable|exists:categories,id',
            'logo_url'          => 'nullable|string|max:255',
            'color'             => 'sometimes|nullable|string|max:7',
            'is_active'         => 'sometimes|boolean',
        ];
    }
}
