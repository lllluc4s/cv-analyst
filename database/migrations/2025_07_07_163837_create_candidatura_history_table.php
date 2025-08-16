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
        Schema::create('candidatura_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidatura_id')->constrained('candidaturas')->onDelete('cascade');
            $table->foreignId('board_state_id')->constrained('board_states')->onDelete('cascade');
            $table->foreignId('previous_state_id')->nullable()->constrained('board_states')->onDelete('set null');
            $table->text('note')->nullable(); // Nota privada
            $table->boolean('email_sent')->default(false);
            $table->json('email_data')->nullable(); // Dados do email enviado (subject, body)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatura_history');
    }
};
