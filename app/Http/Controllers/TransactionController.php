<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListTransactionRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Http\Resources\TransactionResource;
use App\Models\RecurringRule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransactionController extends Controller
{
    use AuthorizesRequests;

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

    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        return new TransactionResource($transaction);
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

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        // Ensure the user owns this transaction
        $this->authorize('update', $transaction);

        // Update the transaction fields
        $transaction->update([
            'amount' => $request->amount,
            'category_id' => $request->category_id,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        // Handle recurring rule (optional for now)
        if ($request->has('recurringRule')) {
            $transaction->recurringRule()->updateOrCreate(
                [],
                $request->recurringRule
            );
        }

        return new TransactionResource($transaction->fresh());
    }

    public function destroy(Transaction $transaction)
    {
        // Ensure the user owns this transaction
        $this->authorize('update', $transaction);
        
        // Delete the transaction
        $transaction->delete();

        return response()->noContent(); // 204
    }
}
