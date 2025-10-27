<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_transaction()
    {
        $transaction = Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100.0,
            'status' => 'pending'
        ]);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals('Alice', $transaction->sender);
        $this->assertEquals('Bob', $transaction->receiver);
        $this->assertEquals(100.0, $transaction->amount);
        $this->assertEquals('pending', $transaction->status);
        $this->assertNotNull($transaction->timestamp);
    }

    /** @test */
    public function it_has_pending_scope()
    {
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        Transaction::create(['sender' => 'Bob', 'receiver' => 'Charlie', 'amount' => 50, 'status' => 'mined']);
        Transaction::create(['sender' => 'Charlie', 'receiver' => 'Dave', 'amount' => 25, 'status' => 'pending']);

        $pendingTransactions = Transaction::pending()->get();

        $this->assertCount(2, $pendingTransactions);
        $this->assertTrue($pendingTransactions->every(fn($t) => $t->status === 'pending'));
    }

    /** @test */
    public function it_has_mined_scope()
    {
        Transaction::create(['sender' => 'Alice', 'receiver' => 'Bob', 'amount' => 100, 'status' => 'pending']);
        Transaction::create(['sender' => 'Bob', 'receiver' => 'Charlie', 'amount' => 50, 'status' => 'mined']);
        Transaction::create(['sender' => 'Charlie', 'receiver' => 'Dave', 'amount' => 25, 'status' => 'mined']);

        $minedTransactions = Transaction::mined()->get();

        $this->assertCount(2, $minedTransactions);
        $this->assertTrue($minedTransactions->every(fn($t) => $t->status === 'mined'));
    }

    /** @test */
    public function it_belongs_to_blocks()
    {
        $transaction = Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100,
            'status' => 'pending'
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $transaction->blocks());
    }

    /** @test */
    public function it_sets_default_timestamp_on_creation()
    {
        $transaction = Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100.0,
            'status' => 'pending'
        ]);

        $this->assertNotNull($transaction->timestamp);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $transaction->timestamp);
    }

    /** @test */
    public function it_can_update_status_from_pending_to_mined()
    {
        $transaction = Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 100.0,
            'status' => 'pending'
        ]);

        $this->assertEquals('pending', $transaction->status);

        $transaction->update(['status' => 'mined']);

        $this->assertEquals('mined', $transaction->fresh()->status);
    }

    /** @test */
    public function it_stores_amount_as_decimal()
    {
        $transaction = Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 123.456,
            'status' => 'pending'
        ]);

        $this->assertIsFloat($transaction->amount);
        $this->assertEquals(123.456, $transaction->amount);
    }

    /** @test */
    public function it_can_have_zero_amount()
    {
        $transaction = Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => 0.0,
            'status' => 'pending'
        ]);

        $this->assertEquals(0.0, $transaction->amount);
    }

    /** @test */
    public function it_can_have_negative_amount()
    {
        // In real blockchain, you might want to prevent this
        // but for testing flexibility
        $transaction = Transaction::create([
            'sender' => 'Alice',
            'receiver' => 'Bob',
            'amount' => -50.0,
            'status' => 'pending'
        ]);

        $this->assertEquals(-50.0, $transaction->amount);
    }
}
