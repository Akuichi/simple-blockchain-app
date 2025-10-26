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
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->integer('index_no')->unique();
            $table->string('previous_hash', 64);
            $table->string('current_hash', 64)->unique();
            $table->bigInteger('nonce')->default(0);
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();
            
            // Index for faster lookups
            $table->index('index_no');
            $table->index('current_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
