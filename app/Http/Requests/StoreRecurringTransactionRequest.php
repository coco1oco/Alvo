<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'type' => 'required|in:income,expense,transfer',
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('user_id', $userId)],
            'to_account_id' => ['nullable', 'required_if:type,transfer', Rule::exists('accounts', 'id')->where('user_id', $userId)],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $userId)],
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'frequency' => 'required|in:daily,weekly,bi-weekly,monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'next_due_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'auto_process' => 'nullable|boolean',
            'is_subscription' => 'nullable|boolean',
            'logo_url' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:30',
        ];
    }
}
