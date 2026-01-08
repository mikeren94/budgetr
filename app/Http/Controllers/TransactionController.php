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

        $rule = null;

        // Create recurring rule if provided
        if (!empty($validated['recurringRule']) && $validated['recurringRule']['isRecurring']) {

            $ruleData = $validated['recurringRule'];

            $rule = RecurringRule::create([
                'user_id' => auth()->id(),
                'category_id' => $validated['category_id'],
                'amount' => $validated['amount'],
                'frequency' => $ruleData['frequency'],
                'interval' => $ruleData['interval'],
                'months' => $ruleData['months'] ?? [],
                'start_date' => $validated['date'],
                'next_occurrence' => $validated['date'],
                'active' => true,
            ]);
        }

        // Create the transaction
        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'category_id' => $validated['category_id'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'recurring_rule_id' => $rule?->id,
            'coverage_end_date' => $validated['coverage_end_date'] ?? null,
        ]);

        return response()->json([
            'message' => 'Transaction created successfully.',
            'data' => $transaction->load('recurringRule'),
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

    public function monthlySummary(Request $request)
    {
        $month = Carbon::parse($request->month ?? now());
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $transactions = Transaction::with('category')
            ->where(function ($q) use ($start, $end) {
                $q->whereHas('category', fn($c) => $c->where('type', 'income'))
                ->whereDate('date', '<=', $end)
                ->whereDate('coverage_end_date', '>=', $start);
            })
            ->orWhere(function ($q) use ($start, $end) {
                $q->whereHas('category', fn($c) => $c->where('type', 'expense'))
                ->whereBetween('date', [$start, $end]);
            })
            ->with('category')->get();

        $income = $transactions->where('category.type', 'income')->sum('amount');
        $expenses = $transactions->where('category.type', 'expense')->sum('amount');

        return response()->json([
            'month' => $month->format('F Y'),
            'income' => $income,
            'expenses' => $expenses,
            'net' => $income - $expenses,
            'transactions' => $transactions,
        ]);
    }
}
