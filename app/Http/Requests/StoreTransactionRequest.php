<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('user_id', $userId)],
            'to_account_id' => ['nullable', Rule::exists('accounts', 'id')->where('user_id', $userId), 'different:account_id'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $userId)],
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
            'is_reimbursable' => 'sometimes|boolean',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:5120',
            'tags' => 'nullable|array',
            'date' => 'required|date',
            'is_split' => 'sometimes|boolean',
            'split_data' => 'nullable|array',
            'split_data.split_mode' => 'sometimes|string|in:equal,custom',
            'split_data.participants' => 'required_if:is_split,1|required_if:is_split,true|array|min:1',
            'split_data.participants.*.name' => 'required_with:split_data.participants|string|max:100',
            'split_data.participants.*.amount' => 'required_with:split_data.participants|numeric|min:0.01',
            'split_data.participants.*.is_settled' => 'sometimes|boolean',
        ];
    }

    /**
     * Add cross-field validation for split expenses.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->boolean('is_split')) {
                return;
            }

            if ($this->input('type') !== 'expense') {
                $validator->errors()->add('is_split', 'Split expenses can only be used for expense transactions.');

                return;
            }

            $participants = data_get($this->input('split_data', []), 'participants', []);

            if (! is_array($participants) || count($participants) < 1) {
                $validator->errors()->add('split_data.participants', 'Add at least one person who owes a share.');

                return;
            }

            $participantTotal = 0.0;
            foreach ($participants as $index => $participant) {
                $name = trim((string) ($participant['name'] ?? ''));
                $amount = (float) ($participant['amount'] ?? 0);

                if ($name === '') {
                    $validator->errors()->add("split_data.participants.$index.name", 'Participant name is required.');
                }

                if ($amount <= 0) {
                    $validator->errors()->add("split_data.participants.$index.amount", 'Participant amount must be greater than zero.');
                }

                $participantTotal += $amount;
            }

            $transactionAmount = (float) $this->input('amount', 0);

            if ($participantTotal - $transactionAmount > 0.01) {
                $validator->errors()->add('split_data.participants', 'Split amounts cannot exceed the transaction total.');
            }
        });
    }
}
