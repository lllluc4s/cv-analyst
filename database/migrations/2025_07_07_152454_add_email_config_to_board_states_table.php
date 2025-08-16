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
        Schema::table('board_states', function (Blueprint $table) {
            $table->boolean('email_enabled')->default(false);
            $table->string('email_subject')->nullable();
            $table->text('email_body')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('board_states', function (Blueprint $table) {
            $table->dropColumn(['email_enabled', 'email_subject', 'email_body']);
        });
    }
};
