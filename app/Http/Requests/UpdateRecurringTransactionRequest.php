<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecurringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'          => 'sometimes|required|in:income,expense,transfer',
            'account_id'    => 'sometimes|required|exists:accounts,id',
            'to_account_id' => 'nullable|exists:accounts,id',
            'category_id'   => 'nullable|exists:categories,id',
            'amount'        => 'sometimes|required|numeric|min:0.01',
            'description'   => 'nullable|string|max:255',
            'frequency'     => 'sometimes|required|in:daily,weekly,bi-weekly,monthly,quarterly,yearly',
            'start_date'    => 'sometimes|required|date',
            'next_due_date' => 'sometimes|required|date',
            'is_active'     => 'sometimes|boolean',
            'auto_process'  => 'sometimes|boolean',
        ];
    }
}
