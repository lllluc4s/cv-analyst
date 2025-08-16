<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Oportunidade;

class OportunidadeTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Oportunidade pública 1
        Oportunidade::create([
            'titulo' => 'Desenvolvedor Frontend Vue.js',
            'descricao' => 'Oportunidade para desenvolver aplicações frontend modernas usando Vue.js, TypeScript e Tailwind CSS. Você trabalhará em um ambiente ágil, colaborando com designers e desenvolvedores backend para criar interfaces de usuário incríveis.',
            'skills_desejadas' => [
                ['nome' => 'Vue.js', 'peso' => 30],
                ['nome' => 'TypeScript', 'peso' => 25],
                ['nome' => 'Tailwind CSS', 'peso' => 20],
                ['nome' => 'Git', 'peso' => 15],
                ['nome' => 'REST APIs', 'peso' => 10]
            ],
            'ativa' => true,
            'publica' => true
        ]);

        // Oportunidade pública 2
        Oportunidade::create([
            'titulo' => 'Desenvolvedor Backend Laravel',
            'descricao' => 'Desenvolvimento de APIs robustas com Laravel, MySQL e experiência em arquitetura de software. Procuramos alguém com paixão por código limpo e boas práticas de desenvolvimento.',
            'skills_desejadas' => [
                ['nome' => 'Laravel', 'peso' => 35],
                ['nome' => 'PHP', 'peso' => 25],
                ['nome' => 'MySQL', 'peso' => 20],
                ['nome' => 'Docker', 'peso' => 15],
                ['nome' => 'Redis', 'peso' => 5]
            ],
            'ativa' => true,
            'publica' => true
        ]);

        // Oportunidade pública 3
        Oportunidade::create([
            'titulo' => 'Full Stack Developer',
            'descricao' => 'Desenvolvedor full stack com experiência em tecnologias modernas. Você será responsável por desenvolver tanto o frontend quanto o backend de nossas aplicações.',
            'skills_desejadas' => [
                ['nome' => 'React', 'peso' => 25],
                ['nome' => 'Node.js', 'peso' => 25],
                ['nome' => 'PostgreSQL', 'peso' => 20],
                ['nome' => 'GraphQL', 'peso' => 15],
                ['nome' => 'AWS', 'peso' => 15]
            ],
            'ativa' => true,
            'publica' => true
        ]);

        // Oportunidade privada (não deve aparecer na listagem pública)
        Oportunidade::create([
            'titulo' => 'DevOps Engineer',
            'descricao' => 'Responsável por infraestrutura cloud, CI/CD e automação de deployments.',
            'skills_desejadas' => [
                ['nome' => 'AWS', 'peso' => 30],
                ['nome' => 'Docker', 'peso' => 25],
                ['nome' => 'Kubernetes', 'peso' => 20],
                ['nome' => 'Terraform', 'peso' => 15],
                ['nome' => 'Jenkins', 'peso' => 10]
            ],
            'ativa' => true,
            'publica' => false // Esta não é pública
        ]);

        // Oportunidade inativa (não deve aparecer)
        Oportunidade::create([
            'titulo' => 'Desenvolvedor Mobile',
            'descricao' => 'Desenvolvimento de aplicações móveis nativas e híbridas.',
            'skills_desejadas' => [
                ['nome' => 'React Native', 'peso' => 40],
                ['nome' => 'Flutter', 'peso' => 30],
                ['nome' => 'Firebase', 'peso' => 20],
                ['nome' => 'REST APIs', 'peso' => 10]
            ],
            'ativa' => false, // Esta está inativa
            'publica' => true
        ]);
    }
}
