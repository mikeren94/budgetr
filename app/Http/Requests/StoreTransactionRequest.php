<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            // Recurring rule fields
            'is_recurring' => ['boolean'],
            'frequency' => ['nullable', 'in:monthly,yearly,custom'],
            'interval' => ['nullable', 'integer', 'min:1'],
            'months' => ['nullable', 'array'],
            'months.*' => ['integer', 'between:1,12'],
        ];
    }

    public function prepareForValidation()
    {
        // Ensure boolean is parsed correctly
        $this->merge([
            'is_recurring' => filter_var($this->is_recurring, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
