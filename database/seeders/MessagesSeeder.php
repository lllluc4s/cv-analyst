<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\Company;
use App\Models\Candidato;
use Carbon\Carbon;

class MessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar a empresa (ID 1)
        $company = Company::first();
        if (!$company) {
            $this->command->error('Nenhuma empresa encontrada! Execute primeiro o seeder de empresas.');
            return;
        }

        // Buscar alguns candidatos para criar conversas
        $candidatos = Candidato::whereIn('id', [1, 2, 3, 4, 5])->get();
        
        if ($candidatos->count() < 5) {
            $this->command->error('Não há candidatos suficientes! Execute primeiro o seeder de candidatos.');
            return;
        }

        // Limpar mensagens existentes
        Message::truncate();

        $this->command->info('🗑️ Mensagens existentes removidas.');

        // Conversa 1: Com candidato ID 1 (Lucas)
        $this->createConversation($company, $candidatos->where('id', 1)->first(), [
            [
                'sender_type' => 'candidato',
                'content' => 'Olá! Fico muito interessado na vaga de Desenvolvedor. Gostaria de saber mais detalhes sobre a posição.',
                'created_at' => Carbon::now()->subDays(3)->subHours(2),
                'read_at' => Carbon::now()->subDays(3)->subHour(),
            ],
            [
                'sender_type' => 'company',
                'content' => 'Olá Lucas! Obrigado pelo interesse. A vaga é para Desenvolvedor Full Stack com foco em Laravel e Vue.js. Você tem experiência nessas tecnologias?',
                'created_at' => Carbon::now()->subDays(3)->subHour(),
                'read_at' => Carbon::now()->subDays(3),
            ],
            [
                'sender_type' => 'candidato',
                'content' => 'Sim, tenho 3 anos de experiência com Laravel e 2 anos com Vue.js. Trabalhei em projetos similares na minha empresa anterior. Posso enviar alguns exemplos do meu trabalho.',
                'created_at' => Carbon::now()->subDays(3),
                'read_at' => Carbon::now()->subDays(2)->subHours(4),
            ],
            [
                'sender_type' => 'company',
                'content' => 'Excelente! Ficaria muito interessado em ver seus trabalhos. Quando você estaria disponível para uma entrevista técnica?',
                'created_at' => Carbon::now()->subDays(2)->subHours(3),
                'read_at' => Carbon::now()->subDays(2)->subHour(),
            ],
            [
                'sender_type' => 'candidato',
                'content' => 'Estou disponível qualquer dia da próxima semana, de preferência no período da manhã. Que tipo de teste técnico vocês costumam aplicar?',
                'created_at' => Carbon::now()->subDays(2),
                'read_at' => null, // Mensagem não lida
            ],
        ]);

        // Conversa 2: Com candidato ID 2 (João)
        $this->createConversation($company, $candidatos->where('id', 2)->first(), [
            [
                'sender_type' => 'company',
                'content' => 'Olá João! Analisamos seu perfil e ficamos impressionados com sua experiência em PHP. Gostaria de conversar sobre uma oportunidade em nossa empresa.',
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'sender_type' => 'candidato',
                'content' => 'Olá! Muito obrigado pelo contato. Sim, tenho muito interesse em conhecer mais sobre a oportunidade. Podem me contar mais sobre a vaga e a empresa?',
                'created_at' => Carbon::now()->subDays(4)->subHours(6),
            ],
            [
                'sender_type' => 'company',
                'content' => 'Claro! Somos uma empresa de tecnologia em crescimento. A vaga é para Senior Developer com foco em arquitetura de sistemas. O salário inicial é competitivo e temos excelentes benefícios.',
                'created_at' => Carbon::now()->subDays(4)->subHours(2),
            ],
            [
                'sender_type' => 'candidato',
                'content' => 'Interessante! Qual stack tecnológico vocês utilizam? E qual seria o modelo de trabalho - presencial, remoto ou híbrido?',
                'created_at' => Carbon::now()->subDays(4),
            ],
        ]);

        // Conversa 3: Com candidato ID 3 (Maria)
        $this->createConversation($company, $candidatos->where('id', 3)->first(), [
            [
                'sender_type' => 'candidato',
                'content' => 'Bom dia! Vi a vaga de Frontend Developer e gostaria de me candidatar. Tenho 4 anos de experiência com React e Vue.js.',
                'created_at' => Carbon::now()->subDays(1)->subHours(8),
                'read_at' => Carbon::now()->subDays(1)->subHours(7),
            ],
            [
                'sender_type' => 'company',
                'content' => 'Bom dia Maria! Obrigado pelo interesse. Sua experiência é exatamente o que procuramos. Você tem experiência com TypeScript também?',
                'created_at' => Carbon::now()->subDays(1)->subHours(6),
                'read_at' => Carbon::now()->subDays(1)->subHours(5),
            ],
            [
                'sender_type' => 'candidato',
                'content' => 'Sim! Uso TypeScript há 2 anos e sou certificada pela Microsoft. Também tenho experiência com testes unitários usando Jest e Cypress.',
                'created_at' => Carbon::now()->subDays(1)->subHours(4),
                'read_at' => null, // Mensagem não lida
            ],
        ]);

        // Conversa 4: Com candidato ID 4 (Pedro)
        $this->createConversation($company, $candidatos->where('id', 4)->first(), [
            [
                'sender_type' => 'company',
                'content' => 'Olá Pedro! Vimos que você aplicou para nossa vaga. Poderia nos contar um pouco sobre sua experiência com metodologias ágeis?',
                'created_at' => Carbon::now()->subHours(12),
            ],
            [
                'sender_type' => 'candidato',
                'content' => 'Olá! Claro! Trabalhei 3 anos usando Scrum e 1 ano com Kanban. Fui Scrum Master em dois projetos e adoro a dinâmica de desenvolvimento ágil.',
                'created_at' => Carbon::now()->subHours(10),
            ],
            [
                'sender_type' => 'company',
                'content' => 'Perfeito! E sobre DevOps? Você tem experiência com CI/CD?',
                'created_at' => Carbon::now()->subHours(8),
            ],
            [
                'sender_type' => 'candidato',
                'content' => 'Sim! Configurei pipelines no GitLab CI e GitHub Actions. Também trabalho com Docker e tenho conhecimentos básicos de AWS.',
                'created_at' => Carbon::now()->subHours(6),
            ],
            [
                'sender_type' => 'company',
                'content' => 'Excelente perfil! Vamos agendar uma entrevista. Você pode na sexta-feira às 14h?',
                'created_at' => Carbon::now()->subHours(2),
            ],
        ]);

        // Conversa 5: Com candidato ID 5 (Ana) - Conversa recente e ativa
        $this->createConversation($company, $candidatos->where('id', 5)->first(), [
            [
                'sender_type' => 'candidato',
                'content' => 'Boa tarde! Acabei de enviar minha candidatura. Sou especialista em UX/UI Design e tenho muito interesse na vaga.',
                'created_at' => Carbon::now()->subHours(3),
                'read_at' => Carbon::now()->subHours(2)->subMinutes(30),
            ],
            [
                'sender_type' => 'company',
                'content' => 'Boa tarde Ana! Obrigado pela candidatura. Seu portfólio no Behance é impressionante! Você tem experiência com design systems?',
                'created_at' => Carbon::now()->subHours(2),
                'read_at' => Carbon::now()->subHour()->subMinutes(30),
            ],
            [
                'sender_type' => 'candidato',
                'content' => 'Muito obrigada! Sim, criei um design system completo na minha empresa atual. Posso mostrar na entrevista se quiserem.',
                'created_at' => Carbon::now()->subHour(),
                'read_at' => Carbon::now()->subMinutes(45),
            ],
            [
                'sender_type' => 'company',
                'content' => 'Seria fantástico! Que tal amanhã às 10h para uma primeira conversa?',
                'created_at' => Carbon::now()->subMinutes(30),
                'read_at' => Carbon::now()->subMinutes(20),
            ],
            [
                'sender_type' => 'candidato',
                'content' => 'Perfeito! Estarei disponível. Vou preparar uma apresentação do meu trabalho. Obrigada pela oportunidade! 😊',
                'created_at' => Carbon::now()->subMinutes(15),
                'read_at' => null, // Mensagem não lida pela empresa
            ],
        ]);

        $this->command->info('✅ Seeders de mensagens criado com sucesso!');
        $this->command->info('📊 Estatísticas:');
        $this->command->info('   • 5 conversas criadas');
        $this->command->info('   • ' . Message::count() . ' mensagens inseridas');
        $this->command->info('   • Mensagens com timestamps realistas');
        $this->command->info('   • ' . Message::whereNull('read_at')->count() . ' mensagens não lidas');
        $this->command->info('   • Conversas com mensagens não lidas: Lucas, Maria, Ana');
    }

    /**
     * Criar uma conversa entre empresa e candidato
     */
    private function createConversation(Company $company, Candidato $candidato, array $messages): void
    {
        foreach ($messages as $messageData) {
            Message::create([
                'company_id' => $company->id,
                'candidato_id' => $candidato->id,
                'sender_type' => $messageData['sender_type'],
                'sender_id' => $messageData['sender_type'] === 'company' ? $company->id : $candidato->id,
                'content' => $messageData['content'],
                'read_at' => $messageData['read_at'] ?? null,
                'created_at' => $messageData['created_at'],
                'updated_at' => $messageData['created_at'],
            ]);
        }

        $this->command->info("✅ Conversa criada com {$candidato->email} (" . count($messages) . " mensagens)");
    }
}
