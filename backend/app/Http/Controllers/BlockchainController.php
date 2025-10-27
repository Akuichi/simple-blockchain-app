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

    /**
     * Rebuild the blockchain from a specific index.
     */
    public function rebuildChain(): JsonResponse
    {
        $validation = $this->blockchainService->validateChain();
        
        if ($validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Chain is already valid - no rebuild needed',
            ], 400);
        }

        // Find the first invalid block
        $firstInvalidIndex = 0;
        if (!empty($validation['errors'])) {
            // Extract block index from first error message
            preg_match('/Block (\d+):/', $validation['errors'][0], $matches);
            $firstInvalidIndex = isset($matches[1]) ? (int)$matches[1] : 1;
        }

        $result = $this->blockchainService->rebuildChain($firstInvalidIndex);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
