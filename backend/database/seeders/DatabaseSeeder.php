<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Block;
use App\Services\BlockchainService;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create genesis block
        $blockchainService = new BlockchainService();
        $blockchainService->createGenesisBlock();
        
        $this->command->info('Genesis block created successfully!');
    }
}
