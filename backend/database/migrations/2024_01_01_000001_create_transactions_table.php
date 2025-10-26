<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('sender');
            $table->string('receiver');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'mined'])->default('pending');
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('status');
            $table->index('sender');
            $table->index('receiver');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
