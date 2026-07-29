<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'          => 'required|in:income,expense,transfer',
            'account_id'    => 'required|exists:accounts,id',
            'to_account_id' => 'nullable|required_if:type,transfer|exists:accounts,id',
            'category_id'   => 'nullable|exists:categories,id',
            'amount'        => 'required|numeric|min:0.01',
            'description'   => 'nullable|string|max:255',
            'frequency'     => 'required|in:daily,weekly,bi-weekly,monthly,quarterly,yearly',
            'start_date'    => 'required|date',
            'next_due_date' => 'nullable|date',
            'is_active'     => 'nullable|boolean',
            'auto_process'  => 'nullable|boolean',
        ];
    }
}
