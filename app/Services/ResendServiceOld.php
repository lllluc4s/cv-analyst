<?php

namespace App\Services;

use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\Log;
use Exception;

class ResendService
{
    protected $enabled;

    public function __construct()
    {
        $this->enabled = config('resend.enabled', false);
    }

    /**
     * Criar ou obter um grupo de audiência existente pelo ID da oportunidade
     * 
     * @param int $oportunidadeId
     * @return string|null ID do grupo de audiência
     */
    public function obterOuCriarGrupoAudiencia($oportunidadeId)
    {
        Log::info("ResendService: obterOuCriarGrupoAudiencia chamado", [
            'oportunidade_id' => $oportunidadeId,
            'enabled' => $this->enabled,
            'has_api_key' => !empty($this->apiKey)
        ]);

        if (!$this->enabled || !$this->apiKey) {
            Log::info('Resend está desativado ou API key não configurada', [
                'enabled' => $this->enabled,
                'has_api_key' => !empty($this->apiKey)
            ]);
            return null;
        }

        try {
            // Primeiro tentamos obter o grupo existente
            $nomeGrupo = "oportunidade-{$oportunidadeId}";
            
            Log::info("Procurando grupo existente: {$nomeGrupo}");
            
            $grupos = $this->listarGruposAudiencia();
            
            Log::info("Grupos encontrados", ['total' => count($grupos)]);
            
            foreach ($grupos as $grupo) {
                if ($grupo['name'] === $nomeGrupo) {
                    Log::info("Grupo existente encontrado", ['grupo_id' => $grupo['id'], 'nome' => $grupo['name']]);
                    return $grupo['id'];
                }
            }
            
            // Se não existir, criamos um novo grupo
            Log::info("Grupo não encontrado, criando novo: {$nomeGrupo}");
            $novoGrupoId = $this->criarGrupoAudiencia($nomeGrupo);
            
            Log::info("Resultado da criação do grupo", ['novo_grupo_id' => $novoGrupoId]);
            
            return $novoGrupoId;
            
        } catch (Exception $e) {
            Log::error('Erro ao obter/criar grupo de audiência no Resend: ' . $e->getMessage(), [
                'oportunidade_id' => $oportunidadeId,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Adicionar um contato a um grupo de audiência
     * 
     * @param string $grupoId
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @return bool
     */
    public function adicionarContatoAoGrupo($grupoId, $email, $firstName, $lastName = '')
    {
        Log::info("ResendService: adicionarContatoAoGrupo chamado", [
            'grupo_id' => $grupoId,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'enabled' => $this->enabled,
            'has_api_key' => !empty($this->apiKey)
        ]);

        if (!$this->enabled || !$this->apiKey || !$grupoId) {
            Log::info('Resend está desativado, API key não configurada ou grupo ID inválido', [
                'enabled' => $this->enabled,
                'has_api_key' => !empty($this->apiKey),
                'grupo_id' => $grupoId
            ]);
            return false;
        }

        try {
            $payload = [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'unsubscribed' => false
            ];
            
            Log::info("Fazendo requisição para API Resend", [
                'url' => "{$this->apiUrl}/audiences/{$grupoId}/contacts",
                'payload' => $payload
            ]);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json'
            ])->post("{$this->apiUrl}/audiences/{$grupoId}/contacts", $payload);
            
            Log::info("Resposta da API Resend", [
                'status' => $response->status(),
                'body' => $response->body(),
                'successful' => $response->successful()
            ]);
            
            if ($response->successful()) {
                Log::info("Contato {$email} adicionado ao grupo {$grupoId} com sucesso");
                return true;
            } else {
                Log::warning("Falha ao adicionar contato ao grupo", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'email' => $email,
                    'grupo_id' => $grupoId
                ]);
                return false;
            }
        } catch (Exception $e) {
            Log::error('Erro ao adicionar contato ao grupo de audiência: ' . $e->getMessage(), [
                'email' => $email,
                'grupo_id' => $grupoId,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Listar todos os grupos de audiência
     * 
     * @return array
     */
    private function listarGruposAudiencia()
    {
        if (!$this->enabled || !$this->apiKey) {
            return [];
        }

        try {
            Log::info("Tentando listar audiências no Resend");
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json'
            ])->get("{$this->apiUrl}/audiences");
            
            Log::info("Resposta da listagem de audiências", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            if ($response->successful()) {
                return $response->json('data', []);
            }
            
            return [];
        } catch (Exception $e) {
            Log::error('Erro ao listar grupos de audiência: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Criar um novo grupo de audiência
     * 
     * @param string $nome
     * @return string|null ID do grupo criado
     */
    private function criarGrupoAudiencia($nome)
    {
        if (!$this->enabled || !$this->apiKey) {
            return null;
        }

        try {
            Log::info("Tentando criar audiência no Resend", ['nome' => $nome]);
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json'
            ])->post("{$this->apiUrl}/audiences", [
                'name' => $nome
            ]);
            
            Log::info("Resposta da criação de audiência", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            if ($response->successful()) {
                $dados = $response->json();
                return $dados['id'] ?? null;
            }
            
            return null;
        } catch (Exception $e) {
            Log::error('Erro ao criar grupo de audiência: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Envia uma campanha de email para um grupo de audiência (simples)
     *
     * @param string $grupoId
     * @param string $assunto
     * @param string $conteudoHtml
     * @param string $remetente
     * @return bool
     */
    public function enviarCampanhaParaGrupo($grupoId, $assunto, $conteudoHtml, $remetente)
    {
        if (!$this->enabled || !$this->apiKey || !$grupoId) {
            Log::info('Resend está desativado, API key não configurada ou grupo ID inválido');
            return false;
        }

        try {
            // Formato exato conforme documentação do Resend
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json'
            ])->post("{$this->apiUrl}/emails", [
                'from' => $remetente,
                'to' => ["audience:{$grupoId}"],
                'subject' => $assunto,
                'html' => $conteudoHtml
            ]);

            if ($response->successful()) {
                Log::info("Campanha enviada para grupo {$grupoId} com sucesso");
                return true;
            } else {
                Log::warning("Falha ao enviar campanha: " . $response->body());
                return false;
            }
        } catch (Exception $e) {
            Log::error('Erro ao enviar campanha: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter todos os contatos de um grupo de audiência
     * 
     * @param string $grupoId
     * @return array
     */
    public function obterContatosDoGrupo($grupoId)
    {
        if (!$this->enabled || !$this->apiKey || !$grupoId) {
            Log::info('Resend está desativado, API key não configurada ou grupo ID inválido');
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json'
            ])->get("{$this->apiUrl}/audiences/{$grupoId}/contacts");
            
            if ($response->successful()) {
                return $response->json('data', []);
            } else {
                Log::warning("Falha ao obter contatos do grupo: " . $response->body());
                return [];
            }
        } catch (Exception $e) {
            Log::error('Erro ao obter contatos do grupo de audiência: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter contato específico por ID ou email
     * 
     * @param string $grupoId
     * @param string $identificador ID ou email do contato
     * @param bool $isEmail Se true, o identificador é um email, caso contrário é um ID
     * @return array|null
     */
    public function obterContato($grupoId, $identificador, $isEmail = false)
    {
        if (!$this->enabled || !$this->apiKey || !$grupoId) {
            Log::info('Resend está desativado, API key não configurada ou grupo ID inválido');
            return null;
        }

        try {
            $params = [];
            if ($isEmail) {
                $params['email'] = $identificador;
            } else {
                $params['id'] = $identificador;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json'
            ])->get("{$this->apiUrl}/audiences/{$grupoId}/contacts", $params);
            
            if ($response->successful()) {
                $contatos = $response->json('data', []);
                if (count($contatos) > 0) {
                    return $contatos[0];
                }
                return null;
            } else {
                Log::warning("Falha ao obter contato: " . $response->body());
                return null;
            }
        } catch (Exception $e) {
            Log::error('Erro ao obter contato do grupo de audiência: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Envia uma campanha de email para um grupo de audiência com template personalizado
     *
     * @param int $oportunidadeId ID da oportunidade
     * @param string $assunto Assunto do email
     * @param string $template Template HTML do email (pode conter variáveis como {{nome}})
     * @param string $remetente Email do remetente
     * @param array $dadosAdicionais Dados adicionais para o template
     * @return array Detalhes da campanha enviada (sucesso, total, falhas)
     */
    public function enviarCampanhaParaOportunidade($oportunidadeId, $assunto, $template, $remetente, $dadosAdicionais = [])
    {
        if (!$this->enabled || !$this->apiKey) {
            Log::info('Resend está desativado ou API key não configurada');
            return ['sucesso' => false, 'mensagem' => 'Serviço Resend desativado ou não configurado'];
        }

        try {
            // Obter ID do grupo da oportunidade
            $grupoId = $this->obterOuCriarGrupoAudiencia($oportunidadeId);
            
            if (!$grupoId) {
                return ['sucesso' => false, 'mensagem' => 'Grupo de audiência não encontrado'];
            }
            
            // Obter todos os contatos do grupo
            $contatos = $this->obterContatosDoGrupo($grupoId);
            
            if (empty($contatos)) {
                return ['sucesso' => false, 'mensagem' => 'Nenhum contato encontrado no grupo'];
            }
            
            // Dados do resultado
            $resultado = [
                'sucesso' => true,
                'total_contatos' => count($contatos),
                'enviados' => 0,
                'falhas' => 0,
                'detalhes' => []
            ];
            
            // Enviar email para o grupo todo
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json'
            ])->post("{$this->apiUrl}/emails", [
                'from' => $remetente,
                'to' => ["audience:{$grupoId}"],
                'subject' => $assunto,
                'html' => $template,
                'tags' => [
                    ['name' => 'oportunidade_id', 'value' => (string)$oportunidadeId],
                    ['name' => 'campanha', 'value' => 'marketing']
                ]
            ]);
            
            if ($response->successful()) {
                $resultado['enviados'] = $resultado['total_contatos'];
                $resultado['id_campanha'] = $response->json('id');
                Log::info("Campanha enviada para oportunidade {$oportunidadeId} com sucesso");
            } else {
                $resultado['sucesso'] = false;
                $resultado['falhas'] = $resultado['total_contatos'];
                $resultado['erro'] = $response->body();
                Log::warning("Falha ao enviar campanha: " . $response->body());
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            Log::error('Erro ao enviar campanha: ' . $e->getMessage());
            return [
                'sucesso' => false, 
                'mensagem' => 'Erro ao enviar campanha: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Simula o fluxo completo de marketing para testes
     * - Cria um grupo para a oportunidade
     * - Adiciona um contato de teste
     * - Envia uma campanha de teste
     *
     * @param int $oportunidadeId
     * @return array Resultado da simulação
     */
    public function simularFluxoCompleto($oportunidadeId)
    {
        $resultado = [
            'sucesso' => true,
            'etapas' => []
        ];

        try {
            // 1. Criar ou obter grupo de audiência
            $grupoId = $this->obterOuCriarGrupoAudiencia($oportunidadeId);
            $resultado['etapas'][] = [
                'etapa' => 'criar_grupo',
                'sucesso' => !empty($grupoId),
                'grupo_id' => $grupoId
            ];

            if (empty($grupoId)) {
                return array_merge($resultado, [
                    'sucesso' => false,
                    'mensagem' => 'Falha ao criar grupo de audiência'
                ]);
            }

            // 2. Adicionar contato de teste
            $emailTeste = 'teste-' . time() . '@example.com';
            $adicionou = $this->adicionarContatoAoGrupo($grupoId, $emailTeste, 'Usuário', 'Teste');
            $resultado['etapas'][] = [
                'etapa' => 'adicionar_contato',
                'sucesso' => $adicionou,
                'email' => $emailTeste
            ];

            // 3. Listar contatos para confirmar
            $contatos = $this->obterContatosDoGrupo($grupoId);
            $encontrouContato = false;
            $contatoId = null;
            foreach ($contatos as $contato) {
                if ($contato['email'] === $emailTeste) {
                    $encontrouContato = true;
                    $contatoId = $contato['id'] ?? null;
                    break;
                }
            }
            $resultado['etapas'][] = [
                'etapa' => 'verificar_contato',
                'sucesso' => $encontrouContato,
                'total_contatos' => count($contatos)
            ];
            
            // 3.1 Atualizar contato se encontrado
            if ($encontrouContato && $contatoId) {
                $atualizou = $this->atualizarContato($grupoId, $contatoId, [
                    'last_name' => 'Teste'
                ]);
                $resultado['etapas'][] = [
                    'etapa' => 'atualizar_contato',
                    'sucesso' => $atualizou,
                    'contato_id' => $contatoId
                ];
            }

            // 4. Enviar campanha de teste
            $templateTeste = '<html><body><h1>Teste de Campanha</h1><p>Este é um email de teste para a oportunidade {{oportunidade_id}}.</p><p>Data e hora: {{data_hora}}</p></body></html>';
            $templateTeste = str_replace(
                ['{{oportunidade_id}}', '{{data_hora}}'],
                [$oportunidadeId, date('Y-m-d H:i:s')],
                $templateTeste
            );

            $resultadoCampanha = $this->enviarCampanhaParaOportunidade(
                $oportunidadeId,
                'Teste de Integração Resend - ' . date('H:i:s'),
                $templateTeste,
                'Teste <noreply@teste.com>'
            );

            $resultado['etapas'][] = [
                'etapa' => 'enviar_campanha',
                'sucesso' => $resultadoCampanha['sucesso'] ?? false,
                'detalhes' => $resultadoCampanha
            ];
            
            // 5. Remover contato de teste (opcional)
            if ($encontrouContato && $contatoId) {
                $removeu = $this->removerContato($grupoId, $contatoId);
                $resultado['etapas'][] = [
                    'etapa' => 'remover_contato',
                    'sucesso' => $removeu,
                    'contato_id' => $contatoId
                ];
            }

            return $resultado;

        } catch (Exception $e) {
            Log::error('Erro na simulação: ' . $e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao simular fluxo: ' . $e->getMessage(),
                'etapas' => $resultado['etapas']
            ];
        }
    }
    
    /**
     * Atualiza um contato existente no grupo de audiência
     * 
     * @param string $grupoId ID do grupo
     * @param string $identificador ID ou email do contato
     * @param array $dados Dados para atualização
     * @param bool $isEmail Se true, o identificador é um email, caso contrário é um ID
     * @return bool
     */
    public function atualizarContato($grupoId, $identificador, $dados, $isEmail = false)
    {
        if (!$this->enabled || !$this->apiKey || !$grupoId) {
            Log::info('Resend está desativado, API key não configurada ou grupo ID inválido');
            return false;
        }

        try {
            $params = [];
            $payload = [];
            
            // Define os parâmetros de busca (id ou email)
            if ($isEmail) {
                $params['email'] = $identificador;
            } else {
                $params['id'] = $identificador;
            }
            
            // Define os dados a serem atualizados
            $payload = $dados;
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json'
            ])->post("{$this->apiUrl}/audiences/{$grupoId}/contacts/{$identificador}", $payload);
            
            if ($response->successful()) {
                Log::info("Contato {$identificador} atualizado no grupo {$grupoId}");
                return true;
            } else {
                Log::warning("Falha ao atualizar contato: " . $response->body());
                return false;
            }
        } catch (Exception $e) {
            Log::error('Erro ao atualizar contato: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove um contato do grupo de audiência
     * 
     * @param string $grupoId ID do grupo
     * @param string $identificador ID ou email do contato
     * @param bool $isEmail Se true, o identificador é um email, caso contrário é um ID
     * @return bool
     */
    public function removerContato($grupoId, $identificador, $isEmail = false)
    {
        if (!$this->enabled || !$this->apiKey || !$grupoId) {
            Log::info('Resend está desativado, API key não configurada ou grupo ID inválido');
            return false;
        }

        try {
            $url = "{$this->apiUrl}/audiences/{$grupoId}/contacts";
            
            if ($isEmail) {
                $url .= "?email=" . urlencode($identificador);
            } else {
                $url .= "/{$identificador}";
            }
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json'
            ])->delete($url);
            
            if ($response->successful()) {
                Log::info("Contato {$identificador} removido do grupo {$grupoId}");
                return true;
            } else {
                Log::warning("Falha ao remover contato: " . $response->body());
                return false;
            }
        } catch (Exception $e) {
            Log::error('Erro ao remover contato: ' . $e->getMessage());
            return false;
        }
    }
}
