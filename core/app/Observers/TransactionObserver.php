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
        // Manually load relationships because the model isn't saved yet
        if ($transaction->category_id) {
            $transaction->setRelation('category', Category::find($transaction->category_id));
        }

        if ($transaction->recurring_rule_id) {
            $transaction->setRelation('recurringRule', RecurringRule::find($transaction->recurring_rule_id));
        }

        // Only income transactions with a recurring rule get a coverage end date
        if (!$transaction->isIncome() || !$transaction->recurringRule) {
            $transaction->coverage_end_date = null;
            return;
        }

        $rule = $transaction->recurringRule;

        $unit = match ($rule->frequency) {
            'daily' => 'day',
            'weekly' => 'week',
            'monthly' => 'month',
            'yearly' => 'year',
            default => 'month', // safe fallback
        };

        $transaction->coverage_end_date = $transaction->date->copy()->add(
            $unit,
            $rule->interval
        );
    }
}