<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

use Illuminate\Pagination\LengthAwarePaginator;

class ListTransactionsAction
{
    public function execute(User $user, ?string $month = null): LengthAwarePaginator
    {
        $query = Transaction::with(['category', 'recurringRule'])
            ->where('user_id', $user->id);

        if ($month) {
            $query->forMonth($month);
        }

        return $query->orderBy('date', 'desc')
            ->paginate(env('DEFAULT_PAGINATION', 10))
            ->through(function ($transaction) {
                $transaction->formatted_date = $transaction->date->format('Y-m-d');
                return $transaction;
            });
    }
}