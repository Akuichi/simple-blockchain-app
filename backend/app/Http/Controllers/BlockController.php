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
                'data' => [
                    'id' => $block->id,
                    'index' => $block->index_no,
                    'previous_hash' => $block->previous_hash,
                    'current_hash' => $block->current_hash,
                    'nonce' => $block->nonce,
                    'timestamp' => $block->timestamp,
                    'transactions' => $block->transactions,
                ],
            ], 200);
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
            ->get()
            ->map(function ($block) {
                return [
                    'id' => $block->id,
                    'index' => $block->index_no,
                    'previous_hash' => $block->previous_hash,
                    'current_hash' => $block->current_hash,
                    'nonce' => $block->nonce,
                    'timestamp' => $block->timestamp,
                    'transactions' => $block->transactions,
                ];
            });

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
            'data' => [
                'id' => $block->id,
                'index' => $block->index_no,
                'previous_hash' => $block->previous_hash,
                'current_hash' => $block->current_hash,
                'nonce' => $block->nonce,
                'timestamp' => $block->timestamp,
                'transactions' => $block->transactions,
            ],
        ]);
    }

    /**
     * Tamper with a block (for demonstration purposes only).
     * This demonstrates how tampering breaks blockchain immutability.
     */
    public function tamper(int $id): JsonResponse
    {
        try {
            $block = Block::find($id);

            if (!$block) {
                return response()->json([
                    'success' => false,
                    'message' => 'Block not found',
                ], 404);
            }

            if ($block->index_no === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot tamper with genesis block',
                ], 400);
            }

            // Tamper with the block by changing its hash
            $originalHash = $block->current_hash;
            $block->current_hash = 'TAMPERED_' . substr($originalHash, 9);
            $block->save();

            return response()->json([
                'success' => true,
                'message' => 'Block tampered successfully (for demonstration)',
                'data' => [
                    'block_id' => $block->id,
                    'index' => $block->index_no,
                    'original_hash' => $originalHash,
                    'tampered_hash' => $block->current_hash,
                    'warning' => 'Blockchain validation will now fail!',
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to tamper with block',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
