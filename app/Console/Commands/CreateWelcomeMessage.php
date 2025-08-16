<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use App\Models\Candidato;
use App\Models\Colaborador;
use App\Models\Company;

class CreateWelcomeMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:create-welcome';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create welcome message for hired employee';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $colaborador = Colaborador::find(1);
        $company = Company::find(1);
        $candidato = Candidato::where('email', $colaborador->email_pessoal)->first();

        $this->info("Criando mensagem de boas-vindas...");
        $this->info("Company ID: {$company->id}");
        $this->info("Candidato ID: {$candidato->id}");
        $this->info("Colaborador: {$colaborador->nome_completo}");

        try {
            $message = Message::create([
                'company_id' => $company->id,
                'candidato_id' => $candidato->id,
                'sender_type' => 'company',
                'sender_id' => $company->id,
                'content' => "🎉 Parabéns! Bem-vindo à equipa {$company->name}!\n\nEstamos muito felizes em tê-lo connosco para a posição de {$colaborador->posicao}. A partir de {$colaborador->data_inicio_contrato->format('d/m/Y')}, farás oficialmente parte da nossa equipa.\n\nSe tiveres alguma dúvida ou precisares de alguma informação, não hesites em contactar-nos através deste chat.\n\nMais uma vez, bem-vindo! 🚀",
                'read_at' => null,
            ]);
            
            $this->info("Mensagem criada com sucesso! ID: {$message->id}");
            
        } catch (\Exception $e) {
            $this->error("Erro ao criar mensagem: " . $e->getMessage());
        }
    }
}
