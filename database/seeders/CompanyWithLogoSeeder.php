<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Oportunidade;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanyWithLogoSeeder extends Seeder
{
    public function run()
    {
        // Criar uma empresa de exemplo com logo
        $company = Company::updateOrCreate(
            ['email' => 'demo@techcorp.com'],
            [
                'name' => 'TechCorp Solutions',
                'email' => 'demo@techcorp.com',
                'password' => Hash::make('demo123'),
                'website' => 'https://techcorp.com',
                'sector' => 'Tecnologia',
                'logo_path' => 'company_logos/demo-logo.png',
                'logo_url' => asset('storage/company_logos/demo-logo.png'),
                'email_verified_at' => now(),
            ]
        );

        // Criar oportunidades para esta empresa
        $oportunidades = [
            [
                'titulo' => 'Desenvolvedor Fullstack Vue.js + Laravel',
                'descricao' => 'Procuramos um desenvolvedor experiente para trabalhar com Vue.js no frontend e Laravel no backend. Você será responsável por desenvolver e manter aplicações web modernas, integrações com APIs e otimização de performance.',
                'skills_desejadas' => [
                    ['nome' => 'Vue.js', 'peso' => 30],
                    ['nome' => 'Laravel', 'peso' => 25],
                    ['nome' => 'JavaScript', 'peso' => 20],
                    ['nome' => 'PHP', 'peso' => 15],
                    ['nome' => 'MySQL', 'peso' => 10]
                ],
                'localizacao' => 'Lisboa, Portugal',
                'ativa' => true,
                'publica' => true,
            ],
            [
                'titulo' => 'Designer UX/UI',
                'descricao' => 'Estamos à procura de um designer criativo para criar experiências digitais incríveis. Você trabalhará em projetos de aplicações web e mobile, criando interfaces intuitivas e centradas no utilizador.',
                'skills_desejadas' => [
                    ['nome' => 'Figma', 'peso' => 35],
                    ['nome' => 'Adobe XD', 'peso' => 25],
                    ['nome' => 'Prototyping', 'peso' => 20],
                    ['nome' => 'User Research', 'peso' => 20]
                ],
                'localizacao' => 'Porto, Portugal',
                'ativa' => true,
                'publica' => true,
            ],
            [
                'titulo' => 'DevOps Engineer',
                'descricao' => 'Procuramos um DevOps Engineer para implementar e manter nossa infraestrutura cloud. Você será responsável por CI/CD, containerização, monitorização e automação de deployments.',
                'skills_desejadas' => [
                    ['nome' => 'AWS', 'peso' => 30],
                    ['nome' => 'Docker', 'peso' => 25],
                    ['nome' => 'Kubernetes', 'peso' => 20],
                    ['nome' => 'Terraform', 'peso' => 15],
                    ['nome' => 'Jenkins', 'peso' => 10]
                ],
                'localizacao' => 'Remoto',
                'ativa' => true,
                'publica' => true,
            ],
            [
                'titulo' => 'Data Scientist',
                'descricao' => 'Oportunidade para Data Scientist com experiência em machine learning e análise de dados. Você trabalhará com grandes volumes de dados para extrair insights e criar modelos preditivos.',
                'skills_desejadas' => [
                    ['nome' => 'Python', 'peso' => 30],
                    ['nome' => 'Machine Learning', 'peso' => 25],
                    ['nome' => 'SQL', 'peso' => 20],
                    ['nome' => 'TensorFlow', 'peso' => 15],
                    ['nome' => 'Pandas', 'peso' => 10]
                ],
                'localizacao' => 'Braga, Portugal',
                'ativa' => true,
                'publica' => true,
            ],
        ];

        foreach ($oportunidades as $oportunidadeData) {
            Oportunidade::updateOrCreate(
                [
                    'titulo' => $oportunidadeData['titulo'],
                    'company_id' => $company->id
                ],
                array_merge($oportunidadeData, ['company_id' => $company->id])
            );
        }

        $this->command->info('Empresa TechCorp Solutions criada com sucesso!');
        $this->command->info('Email: demo@techcorp.com');
        $this->command->info('Password: demo123');
        $this->command->info('Criadas ' . count($oportunidades) . ' oportunidades para a empresa!');
    }
}
