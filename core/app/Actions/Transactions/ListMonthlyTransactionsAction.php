<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use App\Models\RecurringRule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ListMonthlyTransactionsAction
{
    public function execute(User $user, string $month): Collection
    {
        $month = Carbon::parse($month);
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // 1. Real transactions whose DATE is inside the month
        $real = Transaction::with('category')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->get();

        // 2. Virtual occurrences for this month (ALWAYS included)
        $virtual = RecurringRule::where('user_id', $user->id)
            ->where('active', true)
            ->get()
            ->flatMap(fn($rule) => $rule->virtualOccurrencesForMonth($month));

        // Attach category to virtual transactions
        $virtual->each(function ($transaction) {
            $transaction->setRelation('category', $transaction->category()->first());
        });

        // 3. Merge everything
        return $real
            ->concat($virtual)
            ->sortByDesc('date')
            ->values();
    }
}