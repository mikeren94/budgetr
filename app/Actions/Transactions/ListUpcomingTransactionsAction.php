<?php

namespace App\Actions\Transactions;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Collection;
class ListUpcomingTransactionsAction
{
    public function execute(User $user, ?string $endDate = null, ?int $range = null): Collection
    {
        $start = Carbon::now()->startOfYear();
        // If the user passes ?end_date=YYYY-MM-DD
        if ($endDate) {
            $end = Carbon::parse($endDate)->endOfDay();
        }

        // If the user passes ?range=7 or ?range=30
        else if ($range) {
            $end = now()->addDays($range)->endOfDay();
        }

        // Default: end of month
        else {
            $end = now()->endOfMonth();
        }
        
        return $this->getUpcomingTransactions($user, $start, $end);
    }

    private function getUpcomingTransactions(User $user, Carbon $start, Carbon $end)
    {
        return Transaction::with('category')
            ->where('user_id', $user->id)
            ->where('paid', false)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();
    }
}