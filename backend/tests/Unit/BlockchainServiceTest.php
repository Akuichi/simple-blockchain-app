<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BlockchainService;
use App\Models\Block;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BlockchainServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BlockchainService $blockchainService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->blockchainService = new BlockchainService();
    }

    /** @test */
    public function it_can_calculate_hash_correctly()
    {
        $hash = $this->blockchainService->calculateHash(
            0,
            '0',
            now()->timestamp,
            [],
            0
        );

        $this->assertIsString($hash);
        $this->assertEquals(64, strlen($hash)); // SHA256 produces 64 character hex string
    }

    /** @test */
    public function it_produces_consistent_hash_for_same_input()
    {
        $timestamp = now()->timestamp;
        $transactions = [['id' => 1]];

        $hash1 = $this->blockchainService->calculateHash(1, 'abc', $timestamp, $transactions, 0);
        $hash2 = $this->blockchainService->calculateHash(1, 'abc', $timestamp, $transactions, 0);

        $this->assertEquals($hash1, $hash2);
    }

    /** @test */
    public function it_produces_different_hash_for_different_input()
    {
        $timestamp = now()->timestamp;

        $hash1 = $this->blockchainService->calculateHash(1, 'abc', $timestamp, [], 0);
        $hash2 = $this->blockchainService->calculateHash(2, 'abc', $timestamp, [], 0);

        $this->assertNotEquals($hash1, $hash2);
    }

    /** @test */
    public function it_can_perform_proof_of_work()
    {
        // This tests proof of work indirectly through mining
        $this->blockchainService->createGenesisBlock();

        Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100.0,
            'status' => 'pending'
        ]);

        $block = $this->blockchainService->mineBlock();

        // Verify hash starts with correct number of zeros (difficulty = 2)
        $this->assertStringStartsWith('00', $block->current_hash);
    }

    /** @test */
    public function it_can_create_genesis_block()
    {
        $genesisBlock = $this->blockchainService->createGenesisBlock();

        $this->assertInstanceOf(Block::class, $genesisBlock);
        $this->assertEquals(0, $genesisBlock->index_no);
        $this->assertEquals('0', $genesisBlock->previous_hash);
        $this->assertNotNull($genesisBlock->current_hash);
        $this->assertGreaterThanOrEqual(0, $genesisBlock->nonce);
    }

    /** @test */
    public function it_can_mine_block_with_transactions()
    {
        // Create genesis block first
        $this->blockchainService->createGenesisBlock();

        // Create pending transactions
        Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100.0,
            'status' => 'pending'
        ]);

        Transaction::create([
            'sender' => 'Bob',
            'receiver' => 'Charlie',
            'amount' => 50.0,
            'status' => 'pending'
        ]);

        $minedBlock = $this->blockchainService->mineBlock();

        $this->assertInstanceOf(Block::class, $minedBlock);
        $this->assertEquals(1, $minedBlock->index_no);
        $this->assertCount(2, $minedBlock->transactions);
        
        // Verify transactions are marked as mined
        $transaction1 = Transaction::where('sender', 'Alice')->first();
        $transaction2 = Transaction::where('sender', 'Bob')->first();
        $this->assertEquals('mined', $transaction1->status);
        $this->assertEquals('mined', $transaction2->status);
    }

    /** @test */
    public function it_cannot_mine_block_without_pending_transactions()
    {
        // Create genesis block
        $this->blockchainService->createGenesisBlock();

        $result = $this->blockchainService->mineBlock();

        $this->assertNull($result);
    }

    /** @test */
    public function it_validates_correct_blockchain()
    {
        // Create genesis block
        $this->blockchainService->createGenesisBlock();

        // Create and mine a block
        Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100.0,
            'status' => 'pending'
        ]);

        $this->blockchainService->mineBlock();

        $validation = $this->blockchainService->validateChain();

        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);
    }

    /** @test */
    public function it_detects_tampered_block_hash()
    {
        // Create blockchain
        $this->blockchainService->createGenesisBlock();

        Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100.0,
            'status' => 'pending'
        ]);

        $block = $this->blockchainService->mineBlock();

        // Tamper with the block hash
        $block->current_hash = 'tampered_hash_123';
        $block->save();

        $validation = $this->blockchainService->validateChain();

        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);
        $this->assertStringContainsString('Invalid hash', $validation['errors'][0]);
    }

    /** @test */
    public function it_detects_broken_chain_link()
    {
        // Create blockchain
        $this->blockchainService->createGenesisBlock();

        Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100.0,
            'status' => 'pending'
        ]);

        $block = $this->blockchainService->mineBlock();

        // Break the chain link
        $block->previous_hash = 'wrong_previous_hash';
        $block->save();

        $validation = $this->blockchainService->validateChain();

        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);
    }

    /** @test */
    public function it_detects_invalid_proof_of_work()
    {
        // Create blockchain
        $this->blockchainService->createGenesisBlock();

        Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100.0,
            'status' => 'pending'
        ]);

        $block = $this->blockchainService->mineBlock();

        // Make the hash invalid for proof of work
        $block->current_hash = 'ffffffff' . substr($block->current_hash, 8);
        $block->save();

        $validation = $this->blockchainService->validateChain();

        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);
    }

    /** @test */
    public function it_returns_correct_blockchain_stats()
    {
        // Create genesis block
        $this->blockchainService->createGenesisBlock();

        // Create transactions
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        Transaction::create(['sender' => 'Bob', 'receiver' => 'Charlie', 'amount' => 50, 'status' => 'pending']);
        
        // Mine one block
        $this->blockchainService->mineBlock();

        // Create more pending transactions
        Transaction::create(['sender' => 'Charlie', 'receiver' => 'Alice', 'amount' => 25, 'status' => 'pending']);

        $stats = $this->blockchainService->getStats();

        $this->assertEquals(2, $stats['total_blocks']); // Genesis + 1 mined
        $this->assertEquals(3, $stats['total_transactions']);
        $this->assertEquals(1, $stats['pending_transactions']);
        $this->assertEquals(2, $stats['mined_transactions']);
        $this->assertIsArray($stats['last_block']);
        $this->assertEquals(1, $stats['last_block']['index']);
    }

    /** @test */
    public function it_mines_blocks_with_incrementing_index()
    {
        // Create genesis block
        $this->blockchainService->createGenesisBlock();

        // Mine 3 blocks
        for ($i = 0; $i < 3; $i++) {
            Transaction::create([
                'sender' => "Sender{$i}",
                'receiver' => "Receiver{$i}",
                'amount' => 100.0,
                'status' => 'pending'
            ]);

            $block = $this->blockchainService->mineBlock();
            $this->assertEquals($i + 1, $block->index_no);
        }

        $blocks = Block::orderBy('index_no')->get();
        $this->assertCount(4, $blocks); // Genesis + 3 mined
    }

    /** @test */
    public function it_links_blocks_correctly_in_chain()
    {
        // Create genesis block
        $genesis = $this->blockchainService->createGenesisBlock();

        // Mine 2 blocks
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        $block1 = $this->blockchainService->mineBlock();

        Transaction::create(['sender' => 'Bob', 'receiver' => 'Charlie', 'amount' => 50, 'status' => 'pending']);
        $block2 = $this->blockchainService->mineBlock();

        // Verify chain links
        $this->assertEquals($genesis->current_hash, $block1->previous_hash);
        $this->assertEquals($block1->current_hash, $block2->previous_hash);
    }
}
