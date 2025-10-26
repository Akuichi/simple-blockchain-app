<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Services\BlockchainService;
use Illuminate\Http\JsonResponse;

class BlockController extends Controller
{
    private BlockchainService $blockchainService;

    public function __construct(BlockchainService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    /**
     * Mine a new block with pending transactions.
     */
    public function mine(): JsonResponse
    {
        try {
            $block = $this->blockchainService->mineBlock();

            if (!$block) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending transactions to mine',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Block mined successfully',
                'data' => $block,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mine block',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all blocks in the blockchain.
     */
    public function getAll(): JsonResponse
    {
        $blocks = Block::with('transactions')
            ->orderBy('index_no', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $blocks,
            'count' => $blocks->count(),
        ]);
    }

    /**
     * Get a specific block by ID.
     */
    public function getById(int $id): JsonResponse
    {
        $block = Block::with('transactions')->find($id);

        if (!$block) {
            return response()->json([
                'success' => false,
                'message' => 'Block not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $block,
        ]);
    }
}
