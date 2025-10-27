<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\BlockchainController;

Route::prefix('v1')->group(function () {
    // Transaction routes
    Route::post('/transaction', [TransactionController::class, 'create']);
    Route::get('/transactions/pending', [TransactionController::class, 'getPending']);
    Route::get('/transactions', [TransactionController::class, 'getAll']);
    
    // Block routes
    Route::post('/block/mine', [BlockController::class, 'mine']);
    Route::get('/blocks', [BlockController::class, 'getAll']);
    Route::get('/blocks/{id}', [BlockController::class, 'getById']);
    
    // Blockchain routes
    Route::get('/blockchain/validate', [BlockchainController::class, 'validate']);
    Route::get('/blockchain/stats', [BlockchainController::class, 'getStats']);
    Route::post('/blockchain/rebuild', [BlockchainController::class, 'rebuildChain']);
    
    // Tamper route (for demonstration purposes only)
    Route::post('/block/tamper/{id}', [BlockController::class, 'tamper']);
});
