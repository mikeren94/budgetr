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
            'paid' => ['boolean'],
            // Recurring rule (optional)
            'recurring_rule' => ['nullable', 'array'],
            'recurring_rule.isRecurring' => ['required_with:recurring_rule', 'boolean'],
            'recurring_rule.frequency' => ['required_with:recurring_rule', 'string'],
            'recurring_rule.interval' => ['required_with:recurring_rule', 'integer'],
            'recurring_rule.months' => ['nullable', 'array'],
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
