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
        Schema::table('candidatos', function (Blueprint $table) {
            $table->boolean('is_searchable')->default(true)->after('linkedin_url');
            $table->timestamp('deleted_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatos', function (Blueprint $table) {
            $table->dropColumn(['is_searchable', 'deleted_at']);
        });
    }
};
