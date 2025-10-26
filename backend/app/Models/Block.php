<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Block extends Model
{
    protected $fillable = [
        'index_no',
        'previous_hash',
        'current_hash',
        'nonce',
        'timestamp',
    ];

    protected $casts = [
        'index_no' => 'integer',
        'nonce' => 'integer',
        'timestamp' => 'datetime',
    ];

    /**
     * Get the transactions in this block.
     */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'block_transactions');
    }

    /**
     * Get the previous block in the chain.
     */
    public function previousBlock()
    {
        return $this->where('index_no', $this->index_no - 1)->first();
    }

    /**
     * Get the next block in the chain.
     */
    public function nextBlock()
    {
        return $this->where('index_no', $this->index_no + 1)->first();
    }

    /**
     * Check if this is the genesis block.
     */
    public function isGenesisBlock(): bool
    {
        return $this->index_no === 0;
    }
}
