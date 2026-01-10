<?php
namespace App\Actions\Transactions;

use App\Models\Transaction;

class DeleteTransactionAction
{
    public function execute(Transaction $transaction)
    {
        // Delete the transaction
        $transaction->delete();

        return response()->noContent(); // 204
    }
}