<?php

namespace Database\Seeders;

use App\Models\Colaborador;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ColaboradorBirthdateSeeder extends Seeder
{
    /**
     * Popula o campo data_nascimento e foto_url nos colaboradores existentes
     */
    public function run(): void
    {
        $colaboradores = Colaborador::all();
        $today = Carbon::today();
        
        foreach ($colaboradores as $colaborador) {
            // 30% dos colaboradores terão aniversário hoje para fins de teste
            if (rand(1, 100) <= 30) {
                // Aniversário hoje, ano aleatório entre 1970 e 2000
                $year = rand(1970, 2000);
                $birthdate = Carbon::create($year, $today->month, $today->day);
            } else {
                // Data de nascimento aleatória
                $year = rand(1970, 2000);
                $month = rand(1, 12);
                $day = rand(1, 28);
                $birthdate = Carbon::create($year, $month, $day);
            }
            
            // Atribuir foto aleatória
            $gender = rand(0, 1) ? 'men' : 'women';
            $photoId = rand(1, 50);
            $photoUrl = "https://randomuser.me/api/portraits/{$gender}/{$photoId}.jpg";
            
            $colaborador->update([
                'data_nascimento' => $birthdate->format('Y-m-d'),
                'foto_url' => $photoUrl
            ]);
        }
        
        $this->command->info('Dados de aniversário e fotos dos colaboradores atualizados com sucesso!');
    }
}
