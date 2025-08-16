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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('candidato_id')->constrained()->onDelete('cascade');
            $table->enum('sender_type', ['company', 'candidato']);
            $table->foreignId('sender_id');
            $table->text('content');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'candidato_id']);
            $table->index(['sender_type', 'sender_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
