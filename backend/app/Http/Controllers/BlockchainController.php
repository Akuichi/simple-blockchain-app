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

        $statusCode = $validation['valid'] ? 200 : 400;

        return response()->json([
            'success' => $validation['valid'],
            'message' => $validation['valid'] 
                ? 'Blockchain is valid and secure' 
                : 'Blockchain validation failed',
            'data' => $validation,
        ], $statusCode);
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
