<?php

namespace App\Http\Controllers;

use App\Services\BlockchainService;
use Illuminate\Http\JsonResponse;

class BlockchainController extends Controller
{
    private BlockchainService $blockchainService;

    public function __construct(BlockchainService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    /**
     * Validate the entire blockchain.
     */
    public function validate(): JsonResponse
    {
        $validation = $this->blockchainService->validateChain();

        return response()->json([
            'success' => true,
            'data' => $validation,
        ], 200);
    }

    /**
     * Get blockchain statistics.
     */
    public function getStats(): JsonResponse
    {
        $stats = $this->blockchainService->getStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
