<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'target_amount'     => 'required|numeric|min:1',
            'current_amount'    => 'nullable|numeric|min:0',
            'linked_account_id' => 'nullable|exists:accounts,id',
            'deadline'          => 'nullable|date',
            'color'             => 'nullable|string|max:7',
            'icon'              => 'nullable|string|max:50',
        ];
    }
}
