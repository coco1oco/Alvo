<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'billing_cycle' => 'sometimes|required|in:weekly,monthly,yearly',
            'next_renewal_date' => 'sometimes|required|date',
            'account_id' => ['nullable', Rule::exists('accounts', 'id')->where('user_id', $userId)],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $userId)],
            'logo_url' => 'nullable|string|max:255',
            'color' => 'sometimes|nullable|string|max:7',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
