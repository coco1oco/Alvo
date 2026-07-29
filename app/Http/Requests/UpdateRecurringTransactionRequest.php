<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecurringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'type' => 'sometimes|required|in:income,expense,transfer',
            'account_id' => ['sometimes', 'required', Rule::exists('accounts', 'id')->where('user_id', $userId)],
            'to_account_id' => ['nullable', Rule::exists('accounts', 'id')->where('user_id', $userId)],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $userId)],
            'amount' => 'sometimes|required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'frequency' => 'sometimes|required|in:daily,weekly,bi-weekly,monthly,quarterly,yearly',
            'start_date' => 'sometimes|required|date',
            'next_due_date' => 'sometimes|required|date',
            'is_active' => 'sometimes|boolean',
            'auto_process' => 'sometimes|boolean',
        ];
    }
}
