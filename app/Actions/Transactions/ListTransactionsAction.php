<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

class ListTransactionsAction
{
    public function execute(User $user, ?string $month = null): Collection
    {
        $query = Transaction::with(['category', 'recurringRule'])
            ->where('user_id', $user->id);

        if ($month) {
            $query->forMonth($month);
        }

        return $query->orderBy('date', 'desc')->get();
    }
}