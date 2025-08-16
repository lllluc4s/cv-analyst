<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidatura;
use App\Models\Oportunidade;

class CandidaturaSeeder extends Seeder
{
    public function run()
    {
        $oportunidadeBackend = Oportunidade::where('slug', 'desenvolvedor-backend-php')->first();
        $oportunidadeFrontend = Oportunidade::where('slug', 'frontend-vuejs')->first();

        if ($oportunidadeBackend) {
            Candidatura::create([
                'oportunidade_id' => $oportunidadeBackend->id,
                'nome' => 'João Silva',
                'apelido' => 'João',
                'email' => 'joao.silva@email.com',
                'telefone' => '11999999999',
                'cv_path' => 'cvs/cv-joao-silva.pdf',
                'linkedin' => 'https://linkedin.com/in/joaosilva',
                'rgpd_aceito' => true,
                'skills_extraidas' => ['PHP', 'Laravel', 'MySQL'],
                'pontuacao_skills' => 3,
                'slug' => 'joao-silva-1'
            ]);
            Candidatura::create([
                'oportunidade_id' => $oportunidadeBackend->id,
                'nome' => 'Ana Pereira',
                'apelido' => 'Ana',
                'email' => 'ana.pereira@email.com',
                'telefone' => '11988887777',
                'cv_path' => 'cvs/cv-ana-pereira.pdf',
                'linkedin' => 'https://linkedin.com/in/anapereira',
                'rgpd_aceito' => true,
                'skills_extraidas' => ['PHP', 'MySQL'],
                'pontuacao_skills' => 2,
                'slug' => 'ana-pereira-1'
            ]);
            Candidatura::create([
                'oportunidade_id' => $oportunidadeBackend->id,
                'nome' => 'Carlos Souza',
                'apelido' => 'Carlos',
                'email' => 'carlos.souza@email.com',
                'telefone' => '11977776666',
                'cv_path' => 'cvs/cv-carlos-souza.pdf',
                'linkedin' => 'https://linkedin.com/in/carlossouza',
                'rgpd_aceito' => true,
                'skills_extraidas' => ['PHP'],
                'pontuacao_skills' => 1,
                'slug' => 'carlos-souza-1'
            ]);
        }
        if ($oportunidadeFrontend) {
            Candidatura::create([
                'oportunidade_id' => $oportunidadeFrontend->id,
                'nome' => 'Cléo Oliveira',
                'apelido' => 'Cléo',
                'email' => 'cleo.oliveira@email.com',
                'telefone' => '21988888888',
                'cv_path' => 'cvs/cv-cleo-oliveira.pdf',
                'linkedin' => 'https://linkedin.com/in/cleooliveira',
                'rgpd_aceito' => true,
                'skills_extraidas' => ['Vue.js', 'JavaScript'],
                'pontuacao_skills' => 2,
                'slug' => 'cleo-oliveira-1'
            ]);
            Candidatura::create([
                'oportunidade_id' => $oportunidadeFrontend->id,
                'nome' => 'Bruno Lima',
                'apelido' => 'Bruno',
                'email' => 'bruno.lima@email.com',
                'telefone' => '21977776666',
                'cv_path' => 'cvs/cv-bruno-lima.pdf',
                'linkedin' => 'https://linkedin.com/in/brunolima',
                'rgpd_aceito' => true,
                'skills_extraidas' => ['Vue.js', 'TailwindCSS'],
                'pontuacao_skills' => 2,
                'slug' => 'bruno-lima-1'
            ]);
            Candidatura::create([
                'oportunidade_id' => $oportunidadeFrontend->id,
                'nome' => 'Fernanda Costa',
                'apelido' => 'Fernanda',
                'email' => 'fernanda.costa@email.com',
                'telefone' => '21966665555',
                'cv_path' => 'cvs/cv-fernanda-costa.pdf',
                'linkedin' => 'https://linkedin.com/in/fernandacosta',
                'rgpd_aceito' => true,
                'skills_extraidas' => ['JavaScript'],
                'pontuacao_skills' => 1,
                'slug' => 'fernanda-costa-1'
            ]);
        }
    }
}
