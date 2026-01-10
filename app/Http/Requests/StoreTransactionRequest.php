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
    public function rules()
    {
        return [
            'amount' => ['required', 'numeric'],
            'category_id' => ['required', 'exists:categories,id'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'coverage_end_date' => ['nullable', 'date'],
            'paid' => ['required', 'boolean'],
            // Recurring rule (optional)
            'recurringRule' => ['nullable', 'array'],
            'recurringRule.isRecurring' => ['required_with:recurringRule', 'boolean'],
            'recurringRule.frequency' => ['required_with:recurringRule', 'string'],
            'recurringRule.interval' => ['required_with:recurringRule', 'integer'],
            'recurringRule.months' => ['nullable', 'array'],
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
