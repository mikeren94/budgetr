<?php

namespace App\Actions\Transactions;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Resources\TransactionResource;
use App\Models\RecurringRule;

class MonthlySummaryAction
{
    public function execute(User $user, ?string $month): array
    {
        $month = Carbon::parse($month ?? now());
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // 1. Real transactions
        $transactions = $this->getTransactions($user, $start, $end);

        // 2. Virtual recurring transactions
        $virtual = RecurringRule::where('user_id', $user->id)
            ->where('active', true)
            ->get()
            ->flatMap(fn($rule) => $rule->virtualOccurrencesForMonthlySummary($start, $end));

        // 3. Merge real + virtual
        $allTransactions = $transactions->concat($virtual);

        $income   = round($allTransactions->where('category.type', 'income')->sum('amount'), 2);
        $expenses = round($allTransactions->where('category.type', 'expense')->sum('amount'), 2);
        $net      = round($income - $expenses, 2);

        return [
            'month'        => $month->format('F Y'),
            'income'       => $income,
            'expenses'     => $expenses,
            'net'          => $net,
            'transactions' => TransactionResource::collection($allTransactions),
        ];
    }

    private function getTransactions(User $user, Carbon $start, Carbon $end)
    {
        return Transaction::with('category')
            ->where('user_id', $user->id)
            ->where(function ($outer) use ($start, $end) {

                // -------------------------
                // INCOME LOGIC (fixed)
                // -------------------------
                $outer->where(function ($q) use ($start, $end) {
                    $q->whereHas('category', fn($c) => $c->where('type', 'income'))
                        ->where(function ($income) use ($start, $end) {

                            // 1. Income WITH coverage_end_date → must overlap the month
                            $income->where(function ($q2) use ($start, $end) {
                                $q2->whereNotNull('coverage_end_date')
                                    ->whereDate('date', '<=', $end)
                                    ->whereDate('coverage_end_date', '>=', $start);
                            })

                                // 2. Income WITHOUT coverage_end_date → only count if it occurred this month
                                ->orWhere(function ($q3) use ($start, $end) {
                                    $q3->whereNull('coverage_end_date')
                                        ->whereBetween('date', [$start, $end]);
                                });
                        });
                })

                    // -------------------------
                    // EXPENSE LOGIC (unchanged)
                    // -------------------------
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->whereHas('category', fn($c) => $c->where('type', 'expense'))
                            ->whereBetween('date', [$start, $end]);
                    });
            })
            ->get();
    }
}
