<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => 'sometimes|required|string|max:255',
            'target_amount'     => 'sometimes|required|numeric|min:1',
            'current_amount'    => 'sometimes|nullable|numeric|min:0',
            'linked_account_id' => 'nullable|exists:accounts,id',
            'deadline'          => 'nullable|date',
            'color'             => 'sometimes|nullable|string|max:7',
            'icon'              => 'sometimes|nullable|string|max:50',
        ];
    }
}
