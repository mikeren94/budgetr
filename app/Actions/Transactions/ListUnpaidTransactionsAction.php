<?php

namespace App\Actions\Transactions;

use App\Models\User;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Http\Resources\TransactionResource;

class ListUnpaidTransactionsAction
{
    public function execute(User $user)
    {
        return $this->getUnpaidTransactions($user);
    }

    private function getUnpaidTransactions(User $user)
    {
        return Transaction::where('user_id', $user->id)
            ->where('paid', false)
            ->where('date', '<=', Carbon::now())
            ->whereHas('category', function ($query) {
                $query->where('type', 'expense');
            })
            ->orderBy('date', 'asc')
            ->get();
    }
}