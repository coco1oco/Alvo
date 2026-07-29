<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'amount'            => 'required|numeric|min:0.01',
            'billing_cycle'     => 'required|in:weekly,monthly,yearly',
            'next_renewal_date' => 'required|date',
            'account_id'        => 'nullable|exists:accounts,id',
            'category_id'       => 'nullable|exists:categories,id',
            'logo_url'          => 'nullable|string|max:255',
            'color'             => 'nullable|string|max:7',
            'is_active'         => 'nullable|boolean',
        ];
    }
}
