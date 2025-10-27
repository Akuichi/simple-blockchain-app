<?php

namespace App\Services;

use App\Models\Block;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BlockchainService
{
    private int $difficulty;

    public function __construct()
    {
        $this->difficulty = (int) env('BLOCKCHAIN_DIFFICULTY', 2);
    }

    /**
     * Create the genesis block (first block in the chain).
     */
    public function createGenesisBlock(): Block
    {
        // Check if genesis block already exists
        $genesisBlock = Block::where('index_no', 0)->first();
        
        if ($genesisBlock) {
            return $genesisBlock;
        }

        $block = new Block();
        $block->index_no = 0;
        $block->previous_hash = '0';
        $block->timestamp = now();
        $block->nonce = 0;
        
        // Generate hash for genesis block
        $block->current_hash = $this->calculateHash(
            0,
            '0',
            now()->timestamp,
            [],
            0
        );
        
        $block->save();
        
        return $block;
    }

    /**
     * Calculate hash for a block.
     */
    public function calculateHash(
        int $index,
        string $previousHash,
        int|string $timestamp,
        array $transactions,
        int $nonce
    ): string {
        // Convert timestamp to int if it's a string
        if (is_string($timestamp)) {
            $timestamp = strtotime($timestamp) ?: time();
        }
        
        $data = $index . $previousHash . $timestamp . json_encode($transactions) . $nonce;
        return hash('sha256', $data);
    }

    /**
     * Mine a new block with pending transactions.
     */
    public function mineBlock(): ?Block
    {
        return DB::transaction(function () {
            // Get pending transactions
            $pendingTransactions = Transaction::pending()->get();
            
            if ($pendingTransactions->isEmpty()) {
                return null;
            }

            // Get the last block
            $lastBlock = Block::orderBy('index_no', 'desc')->first();
            
            if (!$lastBlock) {
                $lastBlock = $this->createGenesisBlock();
            }

            // Prepare transaction data for hashing
            $transactionData = $pendingTransactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'sender' => $transaction->sender,
                    'receiver' => $transaction->receiver,
                    'amount' => $transaction->amount,
                    'timestamp' => $transaction->timestamp->timestamp,
                ];
            })->toArray();

            // Create new block
            $newBlock = new Block();
            $newBlock->index_no = $lastBlock->index_no + 1;
            $newBlock->previous_hash = $lastBlock->current_hash;
            $newBlock->timestamp = now();
            $newBlock->nonce = 0;

            // Perform proof of work
            $hash = $this->proofOfWork(
                $newBlock->index_no,
                $newBlock->previous_hash,
                $newBlock->timestamp->timestamp,
                $transactionData,
                $newBlock->nonce
            );

            $newBlock->current_hash = $hash['hash'];
            $newBlock->nonce = $hash['nonce'];
            $newBlock->save();

            // Attach transactions to block and mark as mined
            foreach ($pendingTransactions as $transaction) {
                $newBlock->transactions()->attach($transaction->id);
                $transaction->markAsMined();
            }

            return $newBlock->load('transactions');
        });
    }

    /**
     * Proof of Work algorithm - find a valid hash.
     */
    private function proofOfWork(
        int $index,
        string $previousHash,
        int|string $timestamp,
        array $transactions,
        int $startNonce
    ): array {
        $nonce = $startNonce;
        $prefix = str_repeat('0', $this->difficulty);

        while (true) {
            $hash = $this->calculateHash($index, $previousHash, $timestamp, $transactions, $nonce);
            
            if (substr($hash, 0, $this->difficulty) === $prefix) {
                return ['hash' => $hash, 'nonce' => $nonce];
            }
            
            $nonce++;
        }
    }

    /**
     * Validate the entire blockchain.
     */
    public function validateChain(): array
    {
        $blocks = Block::orderBy('index_no', 'asc')->get();
        
        if ($blocks->isEmpty()) {
            return ['valid' => false, 'error' => 'No blocks in chain'];
        }

        $errors = [];

        foreach ($blocks as $index => $block) {
            // Skip genesis block validation for previous hash
            if ($block->index_no === 0) {
                continue;
            }

            // Check if previous block exists
            $previousBlock = $blocks[$index - 1] ?? null;
            
            if (!$previousBlock) {
                $errors[] = "Block {$block->index_no}: Previous block not found";
                continue;
            }

            // Validate previous hash link
            if ($block->previous_hash !== $previousBlock->current_hash) {
                $errors[] = "Block {$block->index_no}: Chain broken - previous hash does not match previous block's hash";
            }

            // Validate current hash
            $transactionData = $block->transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'sender' => $transaction->sender,
                    'receiver' => $transaction->receiver,
                    'amount' => $transaction->amount,
                    'timestamp' => $transaction->timestamp->timestamp,
                ];
            })->toArray();

            $calculatedHash = $this->calculateHash(
                $block->index_no,
                $block->previous_hash,
                $block->timestamp->timestamp,
                $transactionData,
                $block->nonce
            );

            if ($block->current_hash !== $calculatedHash) {
                $errors[] = "Block {$block->index_no}: Invalid hash - hash does not match calculated value";
            }

            // Validate proof of work
            $prefix = str_repeat('0', $this->difficulty);
            if (substr($block->current_hash, 0, $this->difficulty) !== $prefix) {
                $errors[] = "Block {$block->index_no}: Does not meet difficulty requirement";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'blocks_checked' => $blocks->count(),
        ];
    }

    /**
     * Get blockchain statistics.
     */
    public function getStats(): array
    {
        $totalBlocks = Block::count();
        $totalTransactions = Transaction::count();
        $pendingTransactions = Transaction::pending()->count();
        $minedTransactions = Transaction::mined()->count();
        
        $lastBlock = Block::orderBy('index_no', 'desc')->first();
        
        return [
            'total_blocks' => $totalBlocks,
            'total_transactions' => $totalTransactions,
            'pending_transactions' => $pendingTransactions,
            'mined_transactions' => $minedTransactions,
            'last_block' => $lastBlock ? [
                'id' => $lastBlock->id,
                'index' => $lastBlock->index_no,
                'hash' => $lastBlock->current_hash,
                'timestamp' => $lastBlock->timestamp,
            ] : null,
            'difficulty' => $this->difficulty,
        ];
    }

    /**
     * Rebuild the chain from a specific block index forward.
     * This recalculates and re-mines all blocks after tampering.
     */
    public function rebuildChain(int $fromIndex): array
    {
        return DB::transaction(function () use ($fromIndex) {
            $blocks = Block::where('index_no', '>=', $fromIndex)
                ->orderBy('index_no', 'asc')
                ->get();
            
            if ($blocks->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No blocks found to rebuild',
                ];
            }

            $rebuiltCount = 0;
            
            foreach ($blocks as $block) {
                // Get the previous block
                $previousBlock = Block::where('index_no', $block->index_no - 1)->first();
                
                if (!$previousBlock && $block->index_no !== 0) {
                    return [
                        'success' => false,
                        'message' => "Cannot rebuild - previous block not found for block {$block->index_no}",
                    ];
                }

                // Update previous hash
                $block->previous_hash = $previousBlock ? $previousBlock->current_hash : '0';

                // Get transaction data
                $transactionData = $block->transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'sender' => $transaction->sender,
                        'receiver' => $transaction->receiver,
                        'amount' => $transaction->amount,
                        'timestamp' => $transaction->timestamp->timestamp,
                    ];
                })->toArray();

                // Re-mine the block (proof of work)
                $hash = $this->proofOfWork(
                    $block->index_no,
                    $block->previous_hash,
                    $block->timestamp->timestamp,
                    $transactionData,
                    0 // Start nonce from 0
                );

                $block->current_hash = $hash['hash'];
                $block->nonce = $hash['nonce'];
                $block->save();

                $rebuiltCount++;
            }

            return [
                'success' => true,
                'message' => "Successfully rebuilt {$rebuiltCount} block(s)",
                'rebuilt_count' => $rebuiltCount,
                'from_index' => $fromIndex,
            ];
        });
    }
}
