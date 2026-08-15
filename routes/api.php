<?php

use Illuminate\Support\Facades\Route;
use Whilesmart\Transactions\Http\Controllers\TransactionController;

Route::post('transactions/transfer', [TransactionController::class, 'transfer']);
Route::post('transactions/{transaction}/void', [TransactionController::class, 'void']);
Route::apiResource('transactions', TransactionController::class);
