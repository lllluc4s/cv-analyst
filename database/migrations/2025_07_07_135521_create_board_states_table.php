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
        Schema::create('board_states', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cor')->default('#6B7280'); // Cor hexadecimal
            $table->integer('ordem')->default(0);
            $table->boolean('is_default')->default(false); // Estados padrão do sistema
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->timestamps();
            
            // Índices
            $table->index(['company_id', 'ordem']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_states');
    }
};
