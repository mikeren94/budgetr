<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListTransactionRequest;
use App\Http\Requests\StoreTransactionRequest;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Http\Resources\TransactionResource;

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
        $transaction = Transaction::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Transaction created successfully.',
            'data' => $transaction,
        ], 201);
    }
}
