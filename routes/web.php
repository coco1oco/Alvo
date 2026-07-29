<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// SPA entry point — serve the Vue app for all non-API routes
Route::get('/', function () {
    return view('welcome');
});

// Protected API routes (Clerk)
Route::prefix('api')->middleware('clerk')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Accounts (wallets)
    Route::apiResource('accounts', AccountController::class);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Transactions
    Route::get('/transactions/export', [TransactionController::class, 'export']);
    Route::get('/transactions/trashed', [TransactionController::class, 'trashed']);
    Route::post('/transactions/{id}/restore', [TransactionController::class, 'restore']);
    Route::delete('/transactions/{id}/force', [TransactionController::class, 'forceDelete']);
    Route::apiResource('transactions', TransactionController::class);

    // Budgets
    Route::apiResource('budgets', BudgetController::class)->only(['index', 'store', 'destroy']);

    // Recurring Transactions
    Route::post('/recurring-transactions/{recurringTransaction}/process', [RecurringTransactionController::class, 'process']);
    Route::apiResource('recurring-transactions', RecurringTransactionController::class);
});
