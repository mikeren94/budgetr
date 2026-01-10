<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;

class MarkTransactionPaidAction
{
    public function execute(Transaction $transaction): array
    {
        $transaction->update(['paid' => true]);

        return [
            'message' => 'Transaction marked as paid',
        ];
    }
}