<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Block;
use App\Models\Transaction;
use App\Services\BlockchainService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BlockApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create genesis block
        (new BlockchainService())->createGenesisBlock();
    }

    /** @test */
    public function it_can_mine_block_with_pending_transactions()
    {
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        Transaction::create(['sender' => 'Bob', 'receiver' => 'Charlie', 'amount' => 50, 'status' => 'pending']);

        $response = $this->postJson('/api/v1/block/mine');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Block mined successfully'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'index',
                    'previous_hash',
                    'current_hash',
                    'nonce',
                    'timestamp',
                    'transactions'
                ]
            ]);

        // Verify block was created
        $this->assertDatabaseHas('blocks', [
            'index_no' => 1
        ]);

        // Verify transactions were marked as mined
        $this->assertDatabaseHas('transactions', [
            'sender' => 'Alice',
            'status' => 'mined'
        ]);
    }

    /** @test */
    public function it_returns_error_when_no_pending_transactions_to_mine()
    {
        $response = $this->postJson('/api/v1/block/mine');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'No pending transactions to mine'
            ]);
    }

    /** @test */
    public function it_can_get_all_blocks()
    {
        // Genesis block already exists
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        (new BlockchainService())->mineBlock();

        $response = $this->getJson('/api/v1/blocks');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(2, 'data') // Genesis + 1 mined
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'index',
                        'previous_hash',
                        'current_hash',
                        'nonce',
                        'timestamp',
                        'transactions'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_get_block_by_id()
    {
        $genesisBlock = Block::first();

        $response = $this->getJson("/api/v1/blocks/{$genesisBlock->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $genesisBlock->id,
                    'index' => 0
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_block()
    {
        $response = $this->getJson('/api/v1/blocks/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Block not found'
            ]);
    }

    /** @test */
    public function it_includes_transactions_in_block_response()
    {
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        $minedBlock = (new BlockchainService())->mineBlock();

        $response = $this->getJson("/api/v1/blocks/{$minedBlock->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.transactions');
    }

    /** @test */
    public function it_returns_blocks_ordered_by_index()
    {
        // Mine multiple blocks
        for ($i = 0; $i < 3; $i++) {
            Transaction::create([
                'sender' => "Sender{$i}",
                'receiver' => "Receiver{$i}",
                'amount' => 100,
                'status' => 'pending'
            ]);
            (new BlockchainService())->mineBlock();
        }

        $response = $this->getJson('/api/v1/blocks');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEquals(0, $data[0]['index']); // Genesis
        $this->assertEquals(1, $data[1]['index']);
        $this->assertEquals(2, $data[2]['index']);
        $this->assertEquals(3, $data[3]['index']);
    }

    /** @test */
    public function mined_block_has_valid_proof_of_work()
    {
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);

        $response = $this->postJson('/api/v1/block/mine');

        $response->assertStatus(200);
        
        $blockHash = $response->json('data.current_hash');
        
        // Hash should start with "00" (difficulty = 2)
        $this->assertStringStartsWith('00', $blockHash);
    }
}
