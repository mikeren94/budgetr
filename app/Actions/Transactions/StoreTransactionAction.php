<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use App\Models\RecurringRule;
use App\Models\User;
use App\Http\Resources\TransactionResource;

class StoreTransactionAction
{
    public function execute(User $user, array $data): Transaction
    {
        $rule = $this->createRecurringRuleIfNeeded($user, $data);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'date' => $data['date'],
            'description' => $data['description'] ?? null,
            'recurring_rule_id' => $rule?->id,
            'coverage_end_date' => $data['coverage_end_date'] ?? null,
            'paid' => $data['paid'] ?? true,
        ]);

        return $transaction->load('recurringRule');
    }

    private function createRecurringRuleIfNeeded(User $user, array $data): ?RecurringRule
    {
        if (empty($data['recurringRule']) || !$data['recurringRule']['isRecurring']) {
            return null;
        }

        $ruleData = $data['recurringRule'];

        return RecurringRule::create([
            'user_id' => $user->id,
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'frequency' => $ruleData['frequency'],
            'interval' => $ruleData['interval'],
            'months' => $ruleData['months'] ?? [],
            'start_date' => $data['date'],
            'next_occurrence' => $data['date'],
            'active' => true,
        ]);
    }
}