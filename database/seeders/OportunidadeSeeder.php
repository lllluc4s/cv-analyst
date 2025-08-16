<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Oportunidade;

class OportunidadeSeeder extends Seeder
{
    public function run()
    {
        Oportunidade::create([
            'titulo' => 'Desenvolvedor Backend PHP',
            'descricao' => 'Vaga para desenvolvedor backend com experiência em Laravel.',
            'skills_desejadas' => [
                ['nome' => 'PHP', 'peso' => 34],
                ['nome' => 'Laravel', 'peso' => 33],
                ['nome' => 'MySQL', 'peso' => 33],
            ],
            'slug' => 'desenvolvedor-backend-php',
            'ativa' => true
        ]);
        Oportunidade::create([
            'titulo' => 'Frontend Vue.js',
            'descricao' => 'Vaga para desenvolvedor frontend com experiência em Vue.js e Tailwind.',
            'skills_desejadas' => [
                ['nome' => 'Vue.js', 'peso' => 34],
                ['nome' => 'JavaScript', 'peso' => 33],
                ['nome' => 'TailwindCSS', 'peso' => 33],
            ],
            'slug' => 'frontend-vuejs',
            'ativa' => true
        ]);
    }
}
