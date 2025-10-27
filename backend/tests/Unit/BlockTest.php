<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Block;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BlockTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_block()
    {
        $block = Block::create([
            'index_no' => 0,
            'previous_hash' => '0',
            'current_hash' => 'genesis_hash_123',
            'nonce' => 0,
            'timestamp' => now()
        ]);

        $this->assertInstanceOf(Block::class, $block);
        $this->assertEquals(0, $block->index_no);
        $this->assertEquals('0', $block->previous_hash);
        $this->assertEquals('genesis_hash_123', $block->current_hash);
        $this->assertEquals(0, $block->nonce);
    }

    /** @test */
    public function it_has_many_transactions()
    {
        $block = Block::create([
            'index_no' => 1,
            'previous_hash' => 'abc',
            'current_hash' => 'def',
            'nonce' => 123,
            'timestamp' => now()
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $block->transactions());
    }

    /** @test */
    public function it_can_attach_transactions_to_block()
    {
        $block = Block::create([
            'index_no' => 1,
            'previous_hash' => 'abc',
            'current_hash' => 'def',
            'nonce' => 123,
            'timestamp' => now()
        ]);

        $transaction1 = Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100,
            'status' => 'mined'
        ]);

        $transaction2 = Transaction::create([
            'sender' => 'Bob',
            'receiver' => 'Charlie',
            'amount' => 50,
            'status' => 'mined'
        ]);

        $block->transactions()->attach([$transaction1->id, $transaction2->id]);

        $this->assertCount(2, $block->transactions);
        $this->assertTrue($block->transactions->contains($transaction1));
        $this->assertTrue($block->transactions->contains($transaction2));
    }

    /** @test */
    public function it_can_have_zero_transactions()
    {
        $block = Block::create([
            'index_no' => 0,
            'previous_hash' => '0',
            'current_hash' => 'genesis_hash',
            'nonce' => 0,
            'timestamp' => now()
        ]);

        $this->assertCount(0, $block->transactions);
    }

    /** @test */
    public function it_stores_hash_values_correctly()
    {
        $previousHash = 'abc123def456';
        $currentHash = 'def456ghi789';

        $block = Block::create([
            'index_no' => 1,
            'previous_hash' => $previousHash,
            'current_hash' => $currentHash,
            'nonce' => 42,
            'timestamp' => now()
        ]);

        $this->assertEquals($previousHash, $block->previous_hash);
        $this->assertEquals($currentHash, $block->current_hash);
    }

    /** @test */
    public function it_stores_nonce_as_integer()
    {
        $block = Block::create([
            'index_no' => 1,
            'previous_hash' => 'abc',
            'current_hash' => 'def',
            'nonce' => 12345,
            'timestamp' => now()
        ]);

        $this->assertIsInt($block->nonce);
        $this->assertEquals(12345, $block->nonce);
    }

    /** @test */
    public function it_can_retrieve_block_by_index()
    {
        Block::create([
            'index_no' => 0,
            'previous_hash' => '0',
            'current_hash' => 'genesis',
            'nonce' => 0,
            'timestamp' => now()
        ]);

        Block::create([
            'index_no' => 1,
            'previous_hash' => 'genesis',
            'current_hash' => 'block1',
            'nonce' => 100,
            'timestamp' => now()
        ]);

        $block = Block::where('index_no', 1)->first();

        $this->assertEquals(1, $block->index_no);
        $this->assertEquals('block1', $block->current_hash);
    }

    /** @test */
    public function it_orders_blocks_by_index()
    {
        // Create blocks out of order
        Block::create(['index_no' => 2, 'previous_hash' => 'b', 'current_hash' => 'c', 'nonce' => 0, 'timestamp' => now()]);
        Block::create(['index_no' => 0, 'previous_hash' => '0', 'current_hash' => 'a', 'nonce' => 0, 'timestamp' => now()]);
        Block::create(['index_no' => 1, 'previous_hash' => 'a', 'current_hash' => 'b', 'nonce' => 0, 'timestamp' => now()]);

        $blocks = Block::orderBy('index_no')->get();

        $this->assertEquals(0, $blocks[0]->index_no);
        $this->assertEquals(1, $blocks[1]->index_no);
        $this->assertEquals(2, $blocks[2]->index_no);
    }

    /** @test */
    public function it_maintains_timestamp()
    {
        $timestamp = now()->subHours(2);

        $block = Block::create([
            'index_no' => 1,
            'previous_hash' => 'abc',
            'current_hash' => 'def',
            'nonce' => 42,
            'timestamp' => $timestamp
        ]);

        $this->assertEquals($timestamp->format('Y-m-d H:i:s'), $block->timestamp->format('Y-m-d H:i:s'));
    }
}
