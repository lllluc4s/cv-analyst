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
            // Consentimento para receber emails de atualização
            if (!Schema::hasColumn('candidatos', 'consentimento_emails')) {
                $table->boolean('consentimento_emails')->default(false)->after('is_searchable');
                $table->timestamp('consentimento_emails_data')->nullable()->after('consentimento_emails');
                $table->boolean('pode_ser_contactado')->default(false)->after('consentimento_emails_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatos', function (Blueprint $table) {
            $table->dropColumn(['consentimento_emails', 'consentimento_emails_data', 'pode_ser_contactado']);
        });
    }
};
