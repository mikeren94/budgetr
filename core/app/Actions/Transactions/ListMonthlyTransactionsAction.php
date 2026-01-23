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

        $real = Transaction::with('category')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->get();

        $virtual = RecurringRule::where('user_id', $user->id)
            ->where('active', true)
            ->get()
            ->flatMap(fn($rule) => $rule->virtualOccurrencesForMonth($month));

        $virtual->each(function ($transaction) {
            $transaction->setRelation('category', $transaction->category()->first());
        });

        return $real
            ->concat($virtual)
            ->sortByDesc('date')
            ->values();
    }
}