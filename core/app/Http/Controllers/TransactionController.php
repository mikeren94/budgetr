<?php

namespace App\Http\Controllers;

use App\Actions\Transactions\StoreTransactionAction;
use App\Http\Requests\ListTransactionRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Http\Resources\TransactionResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Actions\Transactions\UpdateTransactionAction;
use App\Actions\Transactions\DeleteTransactionAction;
use App\Actions\Transactions\ListTransactionsAction;
use App\Actions\Transactions\ListUnpaidTransactionsAction;
use App\Actions\Transactions\ListUpcomingTransactionsAction;
use App\Actions\Transactions\MonthlySummaryAction;
use App\Actions\Transactions\MarkTransactionPaidAction;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function index(ListTransactionRequest $request, ListTransactionsAction $action)
    {
        return $action->execute($request->user(), [
            'search'  => $request->query('search'),
            'sortBy'  => $request->query('sortBy'),
            'sortDir' => $request->query('sortDir'),
            'month'   => $request->query('month'),
        ]);
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        return new TransactionResource($transaction);
    }

    public function store(StoreTransactionRequest $request, StoreTransactionAction $action)
    {
        $result = $action->execute($request->user(), $request->validated());

        return response()->json([
            'message' => $result['message'],
            'data' => new TransactionResource($result),
        ], 201);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction, UpdateTransactionAction $action)
    {
        $this->authorize('update', $transaction);

        $result = $action->execute($transaction, $request->validated());

        return response()->json([
            'message' => $result['message'],
            'data' => new TransactionResource($result),
        ]);
    }

    public function destroy(Transaction $transaction, DeleteTransactionAction $action)
    {
        $this->authorize('update', $transaction);

        $action->execute($transaction);

        return response()->noContent();
    }

    public function monthlySummary(Request $request, MonthlySummaryAction $action)
    {
        $summary = $action->execute($request->user(), $request->month);

        return response()->json($summary);
    }

    public function unpaid(Request $request, ListUnpaidTransactionsAction $action)
    {
        $transactions = $action->execute($request->user());

        return TransactionResource::collection($transactions);
    }

    public function markAsPaid(Request $request, Transaction $transaction, MarkTransactionPaidAction $action)
    {
        $this->authorize('update', $transaction);

        $result = $action->execute($transaction);

        return response()->json($result);
    }

    public function upcoming(Request $request, ListUpcomingTransactionsAction $action)
    {
        $transactions = $action->execute(
            $request->user(),
            $request->end_date,
            $request->range ? (int) $request->range : null
        );

        return TransactionResource::collection($transactions);
    }
}
