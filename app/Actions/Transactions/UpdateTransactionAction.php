<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use App\Http\Resources\TransactionResource;

class UpdateTransactionAction
{
    public function execute(Transaction $transaction, array $data): Transaction
    {        
        $transaction->update([
            'amount' => $data['amount'],
            'category_id' => $data['category_id'],
            'date' => $data['date'],
            'description' => $data['description'] ?? null,
            'paid' => $data['paid'] ?? $transaction->paid,
        ]);

        if (isset($data['recurringRule'])) {
            $transaction->recurringRule()->updateOrCreate([], $data['recurringRule']);
        }

        return $transaction->load('recurringRule');
    }
}