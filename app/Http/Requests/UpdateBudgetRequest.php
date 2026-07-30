<?php

namespace App\Http\Requests;

use App\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBudgetRequest extends FormRequest
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
        /** @var Budget|null $budget */
        $budget = $this->route('budget');

        return [
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', $this->user()?->id),
                Rule::unique('budgets')
                    ->ignore($budget?->id)
                    ->where(fn ($query) => $query
                        ->where('user_id', $this->user()?->id)
                        ->where('month', $this->input('month'))),
            ],
            'amount' => 'required|numeric|min:0.01',
            'month' => 'required|date_format:Y-m',
        ];
    }
}