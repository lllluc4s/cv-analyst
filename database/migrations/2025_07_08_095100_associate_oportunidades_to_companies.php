<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obter a primeira empresa disponível
        $company = DB::table('companies')->first();
        
        if ($company) {
            // Associar todas as oportunidades sem company_id à primeira empresa
            DB::table('oportunidades')
                ->whereNull('company_id')
                ->update(['company_id' => $company->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não fazemos nada no down, pois não queremos desfazer as associações
    }
};
