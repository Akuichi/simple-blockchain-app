<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Transaction extends Model
{
    protected $fillable = [
        'sender',
        'receiver',
        'amount',
        'status',
        'timestamp',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'timestamp' => 'datetime',
    ];

    /**
     * Get the blocks that contain this transaction.
     */
    public function blocks(): BelongsToMany
    {
        return $this->belongsToMany(Block::class, 'block_transactions');
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include mined transactions.
     */
    public function scopeMined($query)
    {
        return $query->where('status', 'mined');
    }

    /**
     * Mark transaction as mined.
     */
    public function markAsMined(): void
    {
        $this->status = 'mined';
        $this->save();
    }
}
