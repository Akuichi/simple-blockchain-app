<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Block;
use App\Models\Transaction;
use App\Services\BlockchainService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BlockchainApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create genesis block
        (new BlockchainService())->createGenesisBlock();
    }

    /** @test */
    public function it_can_validate_blockchain()
    {
        // Create a valid blockchain
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        (new BlockchainService())->mineBlock();

        $response = $this->getJson('/api/v1/blockchain/validate');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'errors' => []
                ]
            ]);
    }

    /** @test */
    public function it_detects_invalid_blockchain()
    {
        // Create blockchain and tamper with it
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        $block = (new BlockchainService())->mineBlock();

        // Tamper with the block
        $block->current_hash = 'tampered_hash';
        $block->save();

        $response = $this->getJson('/api/v1/blockchain/validate');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'valid' => false
                ]
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'valid',
                    'errors'
                ]
            ]);

        $this->assertNotEmpty($response->json('data.errors'));
    }

    /** @test */
    public function it_can_get_blockchain_stats()
    {
        // Create some transactions and blocks
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        Transaction::create(['sender' => 'Bob', 'receiver' => 'Charlie', 'amount' => 50, 'status' => 'pending']);
        (new BlockchainService())->mineBlock();

        Transaction::create(['sender' => 'Charlie', 'receiver' => 'Dave', 'amount' => 25, 'status' => 'pending']);

        $response = $this->getJson('/api/v1/blockchain/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_blocks' => 2, // Genesis + 1 mined
                    'total_transactions' => 3,
                    'pending_transactions' => 1,
                    'mined_transactions' => 2
                ]
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_blocks',
                    'total_transactions',
                    'pending_transactions',
                    'mined_transactions',
                    'last_block' => [
                        'id',
                        'index',
                        'hash',
                        'timestamp'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_returns_correct_stats_for_empty_blockchain()
    {
        // Only genesis block exists
        $response = $this->getJson('/api/v1/blockchain/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_blocks' => 1,
                    'total_transactions' => 0,
                    'pending_transactions' => 0,
                    'mined_transactions' => 0
                ]
            ]);
    }

    /** @test */
    public function stats_include_last_block_information()
    {
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        $lastBlock = (new BlockchainService())->mineBlock();

        $response = $this->getJson('/api/v1/blockchain/stats');

        $response->assertStatus(200);
        
        $lastBlockData = $response->json('data.last_block');
        
        $this->assertEquals($lastBlock->id, $lastBlockData['id']);
        $this->assertEquals($lastBlock->index_no, $lastBlockData['index']);
        $this->assertEquals($lastBlock->current_hash, $lastBlockData['hash']);
    }

    /** @test */
    public function it_validates_genesis_block()
    {
        $response = $this->getJson('/api/v1/blockchain/validate');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'valid' => true
                ]
            ]);
    }

    /** @test */
    public function it_detects_broken_chain_links()
    {
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        $block = (new BlockchainService())->mineBlock();

        // Break the chain link
        $block->previous_hash = 'wrong_hash';
        $block->save();

        $response = $this->getJson('/api/v1/blockchain/validate');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'valid' => false
                ]
            ]);

        $errors = $response->json('data.errors');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('previous hash does not match', $errors[0]);
    }

    /** @test */
    public function stats_count_increases_with_new_blocks()
    {
        $initialResponse = $this->getJson('/api/v1/blockchain/stats');
        $initialBlocks = $initialResponse->json('data.total_blocks');

        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        (new BlockchainService())->mineBlock();

        $finalResponse = $this->getJson('/api/v1/blockchain/stats');
        $finalBlocks = $finalResponse->json('data.total_blocks');

        $this->assertEquals($initialBlocks + 1, $finalBlocks);
    }

    /** @test */
    public function pending_transactions_decrease_after_mining()
    {
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        Transaction::create(['sender' => 'Bob', 'receiver' => 'Charlie', 'amount' => 50, 'status' => 'pending']);

        $beforeResponse = $this->getJson('/api/v1/blockchain/stats');
        $this->assertEquals(2, $beforeResponse->json('data.pending_transactions'));

        (new BlockchainService())->mineBlock();

        $afterResponse = $this->getJson('/api/v1/blockchain/stats');
        $this->assertEquals(0, $afterResponse->json('data.pending_transactions'));
        $this->assertEquals(2, $afterResponse->json('data.mined_transactions'));
    }
}
