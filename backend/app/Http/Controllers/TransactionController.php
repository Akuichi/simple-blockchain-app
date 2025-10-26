<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Create a new transaction.
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sender' => 'required|string|max:255',
            'receiver' => 'required|string|max:255|different:sender',
            'amount' => 'required|numeric|min:0.01',
        ], [
            'receiver.different' => 'Sender and receiver must be different.',
            'amount.min' => 'Amount must be greater than 0.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $transaction = Transaction::create([
            'sender' => $request->sender,
            'receiver' => $request->receiver,
            'amount' => $request->amount,
            'status' => 'pending',
            'timestamp' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => $transaction,
        ], 201);
    }

    /**
     * Get all pending transactions.
     */
    public function getPending(): JsonResponse
    {
        $transactions = Transaction::pending()
            ->orderBy('timestamp', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'count' => $transactions->count(),
        ]);
    }

    /**
     * Get all transactions.
     */
    public function getAll(): JsonResponse
    {
        $transactions = Transaction::with('blocks')
            ->orderBy('timestamp', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'count' => $transactions->count(),
        ]);
    }
}
