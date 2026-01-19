<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\RecurringRule;

class TransactionObserver
{
    public function saving(Transaction $transaction)
    {
        $this->applyCoverageEndDate($transaction);
    }

    // public function updated(Transaction $transaction)
    // {
    //     $this->applyCoverageEndDate($transaction);
    // }

    protected function applyCoverageEndDate(Transaction $transaction)
    {
        if ($transaction->category_id) {
            $transaction->setRelation('category', Category::find($transaction->category_id));
        }

        if ($transaction->recurring_rule_id) {
            $transaction->setRelation('recurringRule', RecurringRule::find($transaction->recurring_rule_id));
        }

        if (!$transaction->isIncome() || !$transaction->recurringRule) {
            $transaction->coverage_end_date = null;
            return;
        }

        $rule = $transaction->recurringRule;

        if ($rule->frequency === 'monthly') {
            $transaction->coverage_end_date = $transaction->date
                ->copy()
                ->addMonthsNoOverflow($rule->interval);
            return;
        }

        // Fallback for other frequencies (if you even need coverage for them)
        $unit = match ($rule->frequency) {
            'daily' => 'day',
            'weekly' => 'week',
            'yearly' => 'year',
            default => 'month',
        };

        $transaction->coverage_end_date = $transaction->date->copy()->add(
            $unit,
            $rule->interval
        );
    }
}
