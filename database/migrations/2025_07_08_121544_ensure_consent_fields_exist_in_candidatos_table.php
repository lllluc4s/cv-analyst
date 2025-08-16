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
            if (!Schema::hasColumn('candidatos', 'consentimento_emails')) {
                $table->boolean('consentimento_emails')->default(false)->after('is_searchable');
            }
            
            if (!Schema::hasColumn('candidatos', 'consentimento_emails_data')) {
                $table->timestamp('consentimento_emails_data')->nullable()->after('consentimento_emails');
            }
            
            if (!Schema::hasColumn('candidatos', 'pode_ser_contactado')) {
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
            // Não remover os campos no down, pois podem ser usados por outras migrações
        });
    }
};
