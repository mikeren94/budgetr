<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
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
            'amount' => ['required', 'numeric'],
            'category_id' => ['required', 'exists:categories,id'],
            'date' => ['required', 'date'],
            'paid' => ['boolean'],
            'description' => ['nullable', 'string'],
            'recurringRule' => ['nullable', 'array'],
            'recurringRule.isRecurring' => ['boolean'],
            'recurringRule.frequency' => ['nullable', 'string'],
            'recurringRule.interval' => ['nullable', 'integer'],
            'recurringRule.months' => ['nullable', 'array'],
        ];
    }
}
