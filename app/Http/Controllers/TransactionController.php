<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListTransactionRequest;
use App\Http\Requests\StoreTransactionRequest;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Http\Resources\TransactionResource;
use App\Models\RecurringRule;

class TransactionController extends Controller
{
    public function index(ListTransactionRequest $request)
    {
        $query = Transaction::where('user_id', $request->user()->id);

        if ($request->month) {
            $query->forMonth($request->month);
        }

        return TransactionResource::collection(
            $query->orderBy('date', 'desc')->get()
        );
    }

    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();

        // Create the transaction
        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'category_id' => $validated['category_id'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
        ]);

        // Create recurring rule if needed
        if ($request->boolean('is_recurring')) {
            RecurringRule::create([
                'user_id' => auth()->id(),
                'category_id' => $validated['category_id'],
                'amount' => $validated['amount'],
                'frequency' => $validated['frequency'],
                'interval' => $validated['interval'],
                'months' => $validated['months'],
                'start_date' => $validated['date'],
                'next_occurrence' => $validated['date'],
                'active' => true,
            ]);
        }

        return response()->json([
            'message' => 'Transaction created successfully.',
            'data' => $transaction,
        ], 201);
    }
}
