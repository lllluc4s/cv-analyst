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
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->foreignId('board_state_id')->nullable()->constrained('board_states')->onDelete('set null');
            $table->timestamp('moved_to_state_at')->nullable(); // Quando foi movida para este estado
            
            $table->index('board_state_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->dropForeign(['board_state_id']);
            $table->dropColumn(['board_state_id', 'moved_to_state_at']);
        });
    }
};
