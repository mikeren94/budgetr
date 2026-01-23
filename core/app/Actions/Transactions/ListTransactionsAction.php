<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ListTransactionsAction
{
    public function execute(User $user, array $filters = []): LengthAwarePaginator
    {
        $search   = $filters['search']   ?? null;
        $sortBy   = $filters['sortBy']   ?? 'date';
        $sortDir  = $filters['sortDir']  ?? 'desc';
        $month    = $filters['month']    ?? null;

        $query = Transaction::with(['category', 'recurringRule'])
            ->where('user_id', $user->id);

        if ($month) {
            $query->forMonth($month);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('category', fn($c) =>
                      $c->where('name', 'like', "%{$search}%")
                  );
            });
        }

        $allowedSorts = ['date', 'amount', 'description'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'date';
        }

        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        return $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(env('DEFAULT_PAGINATION', 10))
            ->through(function ($transaction) {
                $transaction->formatted_date = $transaction->date->format('Y-m-d');
                return $transaction;
            });
    }
}