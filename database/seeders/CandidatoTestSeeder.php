<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidato;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CandidatoTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista de arquivos CV reais disponíveis
        $cvFiles = [
            'cvs/cv-joao-silva.pdf',
            'cvs/cv-ana-pereira.pdf',
            'cvs/cv-bruno-lima.pdf',
            'cvs/cv-carlos-souza.pdf',
            'cvs/cv-fernanda-costa.pdf',
            'cvs/cv-lucas-rodrigues.pdf',
            'cvs/3FpbUKa3XpbQ8uwotAM2tNOob4IZhP60zdBisMXY.pdf',
            'cvs/4GCjfRV45Nvt9vThdSSxQT9p9MLFmKhQDuo9V7RF.pdf',
            'cvs/B9RdXrSmBdCocNJaHfpw5rLya3DONIDbWdUvB7pw.pdf',
            'cvs/CZ9YnVBkEqnZP3fc7Ip3O8kzUtnjhGilfonubqfO.pdf',
            'cvs/DOjkTHFyxNsL175DdWHwxtyB9q66NLdhUXYIcX2k.pdf',
            'cvs/F7seSLZTbVzT8gPytw81PFPpM0di6JJoHbaxXHE3.pdf',
        ];

        $candidatos = [
            [
                'nome' => 'João',
                'apelido' => 'Silva',
                'email' => 'joao.silva@exemplo.com',
                'password' => Hash::make('password123'),
                'telefone' => '911234567',
                'skills' => ['PHP', 'Laravel', 'Vue.js', 'MySQL', 'Docker', 'Git'],
                'experiencia_profissional' => [
                    [
                        'empresa' => 'TechCorp',
                        'cargo' => 'Desenvolvedor PHP Senior',
                        'periodo' => '2021-2024',
                        'descricao' => 'Desenvolvimento de aplicações web com Laravel e Vue.js, liderança técnica de equipe'
                    ],
                    [
                        'empresa' => 'StartupTech',
                        'cargo' => 'Desenvolvedor Full Stack',
                        'periodo' => '2019-2021',
                        'descricao' => 'Desenvolvimento de MVP e produtos digitais'
                    ]
                ],
                'formacao_academica' => [
                    [
                        'instituicao' => 'Universidade de Lisboa',
                        'curso' => 'Engenharia Informática',
                        'periodo' => '2015-2019',
                        'tipo' => 'Licenciatura'
                    ]
                ],
                'cv_path' => $cvFiles[0],
                'linkedin_url' => 'https://linkedin.com/in/joao-silva-dev',
                'email_verified_at' => now(),
                'is_searchable' => true,
                'is_searchable' => true,
            ],
            [
                'nome' => 'Maria',
                'apelido' => 'Santos',
                'email' => 'maria.santos@exemplo.com',
                'password' => Hash::make('password123'),
                'telefone' => '912345678',
                'skills' => ['JavaScript', 'React', 'Node.js', 'PostgreSQL', 'AWS', 'TypeScript'],
                'experiencia_profissional' => [
                    [
                        'empresa' => 'DigitalStudio',
                        'cargo' => 'Frontend Developer Senior',
                        'periodo' => '2020-2024',
                        'descricao' => 'Desenvolvimento de interfaces modernas com React e TypeScript, mentoria de juniores'
                    ],
                    [
                        'empresa' => 'WebAgency',
                        'cargo' => 'Frontend Developer',
                        'periodo' => '2018-2020',
                        'descricao' => 'Criação de websites responsivos e aplicações web'
                    ]
                ],
                'formacao_academica' => [
                    [
                        'instituicao' => 'Instituto Politécnico do Porto',
                        'curso' => 'Sistemas de Informação',
                        'periodo' => '2014-2018',
                        'tipo' => 'Licenciatura'
                    ]
                ],
                'cv_path' => $cvFiles[1],
                'linkedin_url' => 'https://linkedin.com/in/maria-santos-frontend',
                'email_verified_at' => now(),
                'is_searchable' => true,
                'is_searchable' => true,
            ],
            [
                'nome' => 'Pedro',
                'apelido' => 'Oliveira',
                'email' => 'pedro.oliveira@exemplo.com',
                'password' => Hash::make('password123'),
                'telefone' => '913456789',
                'skills' => ['Python', 'Django', 'PostgreSQL', 'Redis', 'Docker', 'Kubernetes'],
                'experiencia_profissional' => [
                    [
                        'empresa' => 'DataTech Solutions',
                        'cargo' => 'Backend Developer Senior',
                        'periodo' => '2019-2024',
                        'descricao' => 'Desenvolvimento de APIs e microserviços com Python/Django, arquitetura de sistemas'
                    ],
                    [
                        'empresa' => 'CloudSystems',
                        'cargo' => 'Python Developer',
                        'periodo' => '2017-2019',
                        'descricao' => 'Desenvolvimento de aplicações backend e automação'
                    ]
                ],
                'formacao_academica' => [
                    [
                        'instituicao' => 'Universidade do Porto',
                        'curso' => 'Ciências da Computação',
                        'periodo' => '2013-2017',
                        'tipo' => 'Licenciatura'
                    ]
                ],
                'cv_path' => $cvFiles[2],
                'linkedin_url' => 'https://linkedin.com/in/pedro-oliveira-python',
                'email_verified_at' => now(),
                'is_searchable' => true,
            ],
            [
                'nome' => 'Ana',
                'apelido' => 'Costa',
                'email' => 'ana.costa@exemplo.com',
                'password' => Hash::make('password123'),
                'telefone' => '914567890',
                'skills' => ['Java', 'Spring Boot', 'Angular', 'MongoDB', 'Jenkins', 'Git'],
                'experiencia_profissional' => [
                    [
                        'empresa' => 'Enterprise Corp',
                        'cargo' => 'Full Stack Developer Senior',
                        'periodo' => '2018-2024',
                        'descricao' => 'Desenvolvimento de aplicações empresariais com Java/Spring e Angular'
                    ],
                    [
                        'empresa' => 'TechSolutions',
                        'cargo' => 'Java Developer',
                        'periodo' => '2016-2018',
                        'descricao' => 'Desenvolvimento de aplicações web e sistemas corporativos'
                    ]
                ],
                'formacao_academica' => [
                    [
                        'instituicao' => 'Instituto Superior Técnico',
                        'curso' => 'Engenharia de Software',
                        'periodo' => '2012-2016',
                        'tipo' => 'Licenciatura'
                    ]
                ],
                'cv_path' => $cvFiles[3],
                'linkedin_url' => 'https://linkedin.com/in/ana-costa-fullstack',
                'email_verified_at' => now(),
                'is_searchable' => true,
            ],
            [
                'nome' => 'Carlos',
                'apelido' => 'Ferreira',
                'email' => 'carlos.ferreira@exemplo.com',
                'password' => Hash::make('password123'),
                'telefone' => '915678901',
                'skills' => ['AWS', 'Docker', 'Kubernetes', 'Terraform', 'Jenkins', 'Linux'],
                'experiencia_profissional' => [
                    [
                        'empresa' => 'CloudOps Inc',
                        'cargo' => 'DevOps Engineer Senior',
                        'periodo' => '2020-2024',
                        'descricao' => 'Implementação de infraestrutura cloud e pipelines CI/CD, gestão de ambientes'
                    ],
                    [
                        'empresa' => 'InfraTech',
                        'cargo' => 'Systems Administrator',
                        'periodo' => '2018-2020',
                        'descricao' => 'Administração de servidores Linux e automação de processos'
                    ]
                ],
                'formacao_academica' => [
                    [
                        'instituicao' => 'Universidade de Coimbra',
                        'curso' => 'Redes e Sistemas Informáticos',
                        'periodo' => '2014-2018',
                        'tipo' => 'Licenciatura'
                    ]
                ],
                'cv_path' => $cvFiles[4],
                'linkedin_url' => 'https://linkedin.com/in/carlos-ferreira-devops',
                'email_verified_at' => now(),
                'is_searchable' => true,
            ],
            [
                'nome' => 'Sofia',
                'apelido' => 'Rodrigues',
                'email' => 'sofia.rodrigues@exemplo.com',
                'password' => Hash::make('password123'),
                'telefone' => '916789012',
                'skills' => ['Figma', 'Adobe XD', 'Prototyping', 'User Research', 'HTML', 'CSS'],
                'experiencia_profissional' => [
                    [
                        'empresa' => 'Design Studio',
                        'cargo' => 'UX/UI Designer Senior',
                        'periodo' => '2019-2024',
                        'descricao' => 'Design de interfaces e experiências de utilizador, pesquisa de usabilidade'
                    ],
                    [
                        'empresa' => 'Creative Agency',
                        'cargo' => 'UI Designer',
                        'periodo' => '2017-2019',
                        'descricao' => 'Criação de interfaces para web e mobile'
                    ]
                ],
                'formacao_academica' => [
                    [
                        'instituicao' => 'Escola Superior de Artes e Design',
                        'curso' => 'Design de Comunicação',
                        'periodo' => '2013-2017',
                        'tipo' => 'Licenciatura'
                    ]
                ],
                'cv_path' => $cvFiles[5],
                'linkedin_url' => 'https://linkedin.com/in/sofia-rodrigues-ux',
                'email_verified_at' => now(),
                'is_searchable' => true,
            ],
            [
                'nome' => 'Ricardo',
                'apelido' => 'Mendes',
                'email' => 'ricardo.mendes@exemplo.com',
                'password' => Hash::make('password123'),
                'telefone' => '917890123',
                'skills' => ['PHP', 'Laravel', 'MySQL', 'Redis', 'API Development', 'Testing'],
                'experiencia_profissional' => [
                    [
                        'empresa' => 'WebDev Solutions',
                        'cargo' => 'PHP Developer Senior',
                        'periodo' => '2020-2024',
                        'descricao' => 'Desenvolvimento de APIs REST com Laravel, otimização de performance'
                    ],
                    [
                        'empresa' => 'DigitalCorp',
                        'cargo' => 'Backend Developer',
                        'periodo' => '2018-2020',
                        'descricao' => 'Desenvolvimento de sistemas web com PHP e MySQL'
                    ]
                ],
                'formacao_academica' => [
                    [
                        'instituicao' => 'Universidade Nova de Lisboa',
                        'curso' => 'Informática',
                        'periodo' => '2014-2018',
                        'tipo' => 'Licenciatura'
                    ]
                ],
                'cv_path' => $cvFiles[6],
                'linkedin_url' => 'https://linkedin.com/in/ricardo-mendes-php',
                'email_verified_at' => now(),
                'is_searchable' => true,
            ],
            [
                'nome' => 'Catarina',
                'apelido' => 'Pereira',
                'email' => 'catarina.pereira@exemplo.com',
                'password' => Hash::make('password123'),
                'telefone' => '918901234',
                'skills' => ['Vue.js', 'JavaScript', 'CSS', 'SASS', 'Webpack', 'Jest'],
                'experiencia_profissional' => [
                    [
                        'empresa' => 'Frontend Masters',
                        'cargo' => 'Frontend Developer',
                        'periodo' => '2019-2024',
                        'descricao' => 'Desenvolvimento de SPAs com Vue.js, otimização de performance front-end'
                    ],
                    [
                        'empresa' => 'WebStudio',
                        'cargo' => 'Junior Frontend Developer',
                        'periodo' => '2017-2019',
                        'descricao' => 'Criação de interfaces responsivas com HTML, CSS e JavaScript'
                    ]
                ],
                'formacao_academica' => [
                    [
                        'instituicao' => 'Instituto Politécnico de Setúbal',
                        'curso' => 'Tecnologias da Informação',
                        'periodo' => '2013-2017',
                        'tipo' => 'Licenciatura'
                    ]
                ],
                'cv_path' => $cvFiles[7],
                'linkedin_url' => 'https://linkedin.com/in/catarina-pereira-frontend',
                'email_verified_at' => now(),
                'is_searchable' => true,
            ],
            [
                'nome' => 'Luís',
                'apelido' => 'Martins',
                'email' => 'luis.martins@exemplo.com',
                'password' => Hash::make('password123'),
                'telefone' => '919012345',
                'skills' => ['C#', '.NET', 'SQL Server', 'Entity Framework', 'Azure', 'Web API'],
                'experiencia_profissional' => [
                    [
                        'empresa' => 'Microsoft Partner',
                        'cargo' => '.NET Developer Senior',
                        'periodo' => '2020-2024',
                        'descricao' => 'Desenvolvimento de aplicações empresariais com .NET Core e Azure'
                    ],
                    [
                        'empresa' => 'Software House',
                        'cargo' => 'C# Developer',
                        'periodo' => '2018-2020',
                        'descricao' => 'Desenvolvimento de sistemas desktop e web com .NET Framework'
                    ]
                ],
                'formacao_academica' => [
                    [
                        'instituicao' => 'Universidade de Aveiro',
                        'curso' => 'Engenharia Informática',
                        'periodo' => '2014-2018',
                        'tipo' => 'Licenciatura'
                    ]
                ],
                'cv_path' => $cvFiles[8],
                'linkedin_url' => 'https://linkedin.com/in/luis-martins-dotnet',
                'email_verified_at' => now(),
                'is_searchable' => true,
            ],
            [
                'nome' => 'Teresa',
                'apelido' => 'Gonçalves',
                'email' => 'teresa.goncalves@exemplo.com',
                'password' => Hash::make('password123'),
                'telefone' => '920123456',
                'skills' => ['QA', 'Selenium', 'Cypress', 'Testing', 'Automation', 'JIRA'],
                'experiencia_profissional' => [
                    [
                        'empresa' => 'QualityTech',
                        'cargo' => 'QA Engineer Senior',
                        'periodo' => '2019-2024',
                        'descricao' => 'Automação de testes e garantia de qualidade em aplicações web e mobile'
                    ],
                    [
                        'empresa' => 'TestLab',
                        'cargo' => 'Test Analyst',
                        'periodo' => '2017-2019',
                        'descricao' => 'Testes manuais e elaboração de planos de teste'
                    ]
                ],
                'formacao_academica' => [
                    [
                        'instituicao' => 'Instituto Superior de Engenharia do Porto',
                        'curso' => 'Informática',
                        'periodo' => '2013-2017',
                        'tipo' => 'Licenciatura'
                    ]
                ],
                'cv_path' => $cvFiles[9],
                'linkedin_url' => 'https://linkedin.com/in/teresa-goncalves-qa',
                'email_verified_at' => now(),
                'is_searchable' => true,
            ],
            [
                'nome' => 'Bruno',
                'apelido' => 'Lopes',
                'email' => 'bruno.lopes@exemplo.com',
                'password' => Hash::make('password123'),
                'telefone' => '921234567',
                'skills' => ['React Native', 'Flutter', 'Mobile Development', 'iOS', 'Android', 'JavaScript'],
                'experiencia_profissional' => [
                    [
                        'empresa' => 'MobileDev Studio',
                        'cargo' => 'Mobile Developer Senior',
                        'periodo' => '2020-2024',
                        'descricao' => 'Desenvolvimento de aplicações móveis cross-platform com React Native e Flutter'
                    ],
                    [
                        'empresa' => 'AppFactory',
                        'cargo' => 'iOS Developer',
                        'periodo' => '2018-2020',
                        'descricao' => 'Desenvolvimento de aplicações nativas para iOS'
                    ]
                ],
                'formacao_academica' => [
                    [
                        'instituicao' => 'Universidade do Minho',
                        'curso' => 'Engenharia de Sistemas Informáticos',
                        'periodo' => '2014-2018',
                        'tipo' => 'Licenciatura'
                    ]
                ],
                'cv_path' => $cvFiles[10],
                'linkedin_url' => 'https://linkedin.com/in/bruno-lopes-mobile',
                'email_verified_at' => now(),
                'is_searchable' => true,
            ],
            [
                'nome' => 'Inês',
                'apelido' => 'Ramos',
                'email' => 'ines.ramos@exemplo.com',
                'password' => Hash::make('password123'),
                'telefone' => '922345678',
                'skills' => ['Data Science', 'Python', 'Machine Learning', 'Pandas', 'TensorFlow', 'SQL'],
                'experiencia_profissional' => [
                    [
                        'empresa' => 'DataInsights Corp',
                        'cargo' => 'Data Scientist',
                        'periodo' => '2020-2024',
                        'descricao' => 'Análise de dados e desenvolvimento de modelos de machine learning'
                    ],
                    [
                        'empresa' => 'Analytics Lab',
                        'cargo' => 'Data Analyst',
                        'periodo' => '2018-2020',
                        'descricao' => 'Análise estatística e criação de dashboards'
                    ]
                ],
                'formacao_academica' => [
                    [
                        'instituicao' => 'Faculdade de Ciências da Universidade de Lisboa',
                        'curso' => 'Matemática Aplicada',
                        'periodo' => '2014-2018',
                        'tipo' => 'Licenciatura'
                    ]
                ],
                'cv_path' => $cvFiles[11],
                'linkedin_url' => 'https://linkedin.com/in/ines-ramos-datascience',
                'email_verified_at' => now(),
                'is_searchable' => true,
            ],
        ];

        foreach ($candidatos as $candidatoData) {
            Candidato::updateOrCreate(
                ['email' => $candidatoData['email']],
                $candidatoData
            );
        }

        $this->command->info('Candidatos de teste criados com sucesso!');
    }
}
