<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Transaction;
use App\Services\BlockchainService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create genesis block
        (new BlockchainService())->createGenesisBlock();
    }

    /** @test */
    public function it_can_create_transaction_via_api()
    {
        $response = $this->postJson('/api/v1/transaction', [
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100.50
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Transaction created successfully'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'sender', 'receiver', 'amount', 'status', 'timestamp']
            ]);

        $this->assertDatabaseHas('transactions', [
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100.50,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_transaction()
    {
        $response = $this->postJson('/api/v1/transaction', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sender', 'receiver', 'amount']);
    }

    /** @test */
    public function it_validates_amount_is_numeric()
    {
        $response = $this->postJson('/api/v1/transaction', [
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 'not-a-number'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function it_validates_amount_is_positive()
    {
        $response = $this->postJson('/api/v1/transaction', [
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => -100
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function it_can_get_pending_transactions()
    {
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        Transaction::create(['sender' => 'Bob', 'receiver' => 'Charlie', 'amount' => 50, 'status' => 'pending']);
        Transaction::create(['sender' => 'Charlie', 'receiver' => 'Dave', 'amount' => 25, 'status' => 'mined']);

        $response = $this->getJson('/api/v1/transactions/pending');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'sender', 'receiver', 'amount', 'status', 'timestamp']
                ]
            ]);
    }

    /** @test */
    public function it_can_get_all_transactions()
    {
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        Transaction::create(['sender' => 'Bob', 'receiver' => 'Charlie', 'amount' => 50, 'status' => 'mined']);
        Transaction::create(['sender' => 'Charlie', 'receiver' => 'Dave', 'amount' => 25, 'status' => 'pending']);

        $response = $this->getJson('/api/v1/transactions');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_returns_empty_array_when_no_pending_transactions()
    {
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'mined']);

        $response = $this->getJson('/api/v1/transactions/pending');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }

    /** @test */
    public function it_returns_transactions_ordered_by_timestamp()
    {
        $old = Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        $old->timestamp = now()->subHours(2);
        $old->save();

        $recent = Transaction::create(['sender' => 'Bob', 'receiver' => 'Charlie', 'amount' => 50, 'status' => 'pending']);

        $response = $this->getJson('/api/v1/transactions');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEquals($old->id, $data[0]['id']); // Oldest first
        $this->assertEquals($recent->id, $data[1]['id']);
    }
}
