<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Colaborador;
use Carbon\Carbon;

class ColaboradorBirthdaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obter todos os colaboradores
        $colaboradores = Colaborador::all();
        
        if ($colaboradores->isEmpty()) {
            $this->command->info('Nenhum colaborador encontrado. Execute primeiro o seeder de colaboradores.');
            return;
        }
        
        $hoje = Carbon::now();
        $count = 0;
        
        foreach ($colaboradores as $colaborador) {
            // Gerar uma data de nascimento aleatória
            $ano = rand(1985, 2000);
            $mes = rand(1, 12);
            $dia = rand(1, 28);
            
            // 30% de chance de ser aniversário hoje (para testar)
            if (rand(1, 100) <= 30) {
                $mes = $hoje->month;
                $dia = $hoje->day;
                $count++;
            }
            
            $dataNascimento = Carbon::create($ano, $mes, $dia);
            
            // Também adicionar uma foto aleatória (avatar placeholder)
            $fotoUrl = "https://ui-avatars.com/api/?name=" . urlencode($colaborador->candidatura->nome) . "&background=random&color=fff&size=200";
            
            $colaborador->update([
                'data_nascimento' => $dataNascimento,
                'foto_url' => $fotoUrl
            ]);
        }
        
        $this->command->info("Atualizados {$colaboradores->count()} colaboradores com datas de nascimento.");
        $this->command->info("Colaboradores com aniversário hoje: {$count}");
    }
}
