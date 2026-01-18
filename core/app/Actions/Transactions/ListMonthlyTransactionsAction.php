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

        // 1. Real transactions
        $real = Transaction::with('category')
            ->where('user_id', $user->id)
            ->where(function ($q) use ($start, $end) {

                // -------------------------
                // INCOME
                // -------------------------
                $q->where(function ($income) use ($start, $end) {
                    $income->whereHas('category', fn($c) => $c->where('type', 'income'))
                           ->where(function ($branch) use ($start, $end) {

                               // Income WITH coverage_end_date
                               $branch->where(function ($q2) use ($start, $end) {
                                   $q2->whereNotNull('coverage_end_date')
                                      ->whereDate('date', '<=', $end)
                                      ->whereDate('coverage_end_date', '>=', $start);
                               })

                               // Income WITHOUT coverage_end_date → only if inside month
                               ->orWhere(function ($q3) use ($start, $end) {
                                   $q3->whereNull('coverage_end_date')
                                      ->whereBetween('date', [$start, $end]);
                               });
                           });
                })

                // -------------------------
                // EXPENSES
                // -------------------------
                ->orWhere(function ($expense) use ($start, $end) {
                    $expense->whereHas('category', fn($c) => $c->where('type', 'expense'))
                            ->whereBetween('date', [$start, $end]);
                });
            })
            ->get();

        // 2. Virtual transactions
        $virtual = RecurringRule::where('user_id', $user->id)
            ->where('active', true)
            ->get()
            ->flatMap(fn($rule) => $rule->virtualOccurrencesForMonthlySummary($start, $end));

        // 3. Merge + sort
        return $real
            ->concat($virtual)
            ->sortByDesc('date')
            ->values();
    }
}