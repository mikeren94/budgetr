<?php

namespace App\Actions\Transactions;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Models\User;

class MonthlySummaryAction
{
    public function execute(User $user): array
    {
        $month = Carbon::parse($request->month ?? now());
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $transactions = $this->getTransactions($user, $start, $end);

        $income = $transactions->where('category.type', 'income')->sum('amount');
        $expenses = $transactions->where('category.type', 'expense')->sum('amount');

        return [
            'month' => $month->format('F Y'),
            'income' => $income,
            'expenses' => $expenses,
            'net' => round($income - $expenses, 2),
            'transactions' => $transactions,
        ];
    }

    private function getTransactions(User $user, Carbon $start, Carbon $end)
    {
        return Transaction::with('category')
            ->where('user_id', $user->id)
            ->where(function ($outer) use ($start, $end) {
                $outer->where(function ($q) use ($start, $end) {
                    $q->whereHas('category', fn($c) => $c->where('type', 'income'))
                    ->whereDate('date', '<=', $end)
                    ->whereDate('coverage_end_date', '>=', $start);
                })
                ->orWhere(function ($q) use ($start, $end) {
                    $q->whereHas('category', fn($c) => $c->where('type', 'expense'))
                    ->whereBetween('date', [$start, $end]);
                });
            })
            ->get();
    }
}