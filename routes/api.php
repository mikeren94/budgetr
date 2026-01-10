<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','auth'])->group(function() {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/unpaid', [TransactionController::class, 'unpaid']);
    route::get('/transactions/upcoming', [TransactionController::class, 'upcoming']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
    Route::put('/transactions/{transaction}/mark-paid', [TransactionController::class, 'markAsPaid']);
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

    Route::get('/monthly-summary', [TransactionController::class, 'monthlySummary']);
});