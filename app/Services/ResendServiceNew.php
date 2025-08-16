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
        if (!$this->enabled) {
            Log::info('Resend está desativado');
            return null;
        }

        try {
            // Primeiro tentamos obter o grupo existente
            $nomeGrupo = "oportunidade-{$oportunidadeId}";
            
            $grupos = $this->listarGruposAudiencia();
            
            foreach ($grupos as $grupo) {
                // $grupo pode ser um objeto ou array
                $grupoName = is_object($grupo) && isset($grupo->name) ? $grupo->name : 
                            (is_array($grupo) && isset($grupo['name']) ? $grupo['name'] : '');
                $grupoId = is_object($grupo) && isset($grupo->id) ? $grupo->id : 
                          (is_array($grupo) && isset($grupo['id']) ? $grupo['id'] : '');
                
                if ($grupoName === $nomeGrupo) {
                    Log::info("Grupo de audiência encontrado: {$grupoId} para oportunidade {$oportunidadeId}");
                    return $grupoId;
                }
            }
            
            // Se não existir, criamos um novo grupo
            $grupoId = $this->criarGrupoAudiencia($nomeGrupo);
            Log::info("Novo grupo de audiência criado: {$grupoId} para oportunidade {$oportunidadeId}");
            return $grupoId;
            
        } catch (Exception $e) {
            Log::error('Erro ao obter/criar grupo de audiência no Resend: ' . $e->getMessage());
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
        if (!$this->enabled || !$grupoId) {
            Log::info('Resend está desativado ou grupo ID inválido');
            return false;
        }

        try {
            Log::info("Tentando adicionar contato {$email} ao grupo {$grupoId}");
            
            $result = Resend::contacts()->create($grupoId, [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'unsubscribed' => false
            ]);
            
            if ($result) {
                // O resultado pode ser um objeto Contact do Resend
                $contactId = is_object($result) && isset($result->id) ? $result->id : 
                            (is_array($result) && isset($result['id']) ? $result['id'] : 'unknown');
                
                Log::info("Contato {$email} adicionado ao grupo {$grupoId} com sucesso. Contact ID: {$contactId}");
                return true;
            } else {
                Log::warning("Falha ao adicionar contato ao grupo - resultado nulo");
                return false;
            }
        } catch (Exception $e) {
            Log::error('Erro ao adicionar contato ao grupo de audiência: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
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
        if (!$this->enabled) {
            return [];
        }

        try {
            Log::info('Listando grupos de audiência');
            $result = Resend::audiences()->list();
            Log::info('Grupos encontrados: ' . json_encode($result));
            
            // O resultado pode ser um objeto Collection do Resend
            if (is_object($result) && isset($result->data)) {
                return $result->data;
            } elseif (is_array($result) && isset($result['data'])) {
                return $result['data'];
            } else {
                return [];
            }
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
        if (!$this->enabled) {
            return null;
        }

        try {
            Log::info("Criando novo grupo de audiência: {$nome}");
            $result = Resend::audiences()->create([
                'name' => $nome
            ]);
            
            Log::info('Resultado da criação do grupo: ' . json_encode($result));
            
            // O resultado pode ser um objeto Audience do Resend
            if (is_object($result) && isset($result->id)) {
                return $result->id;
            } elseif (is_array($result) && isset($result['id'])) {
                return $result['id'];
            } else {
                return null;
            }
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
        if (!$this->enabled || !$grupoId) {
            Log::info('Resend está desativado ou grupo ID inválido');
            return false;
        }

        try {
            $result = Resend::emails()->send([
                'from' => $remetente,
                'to' => ["audience:{$grupoId}"],
                'subject' => $assunto,
                'html' => $conteudoHtml
            ]);

            if ($result) {
                Log::info("Campanha enviada para grupo {$grupoId} com sucesso");
                return true;
            } else {
                Log::warning("Falha ao enviar campanha");
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
        if (!$this->enabled || !$grupoId) {
            Log::info('Resend está desativado ou grupo ID inválido');
            return [];
        }

        try {
            $result = Resend::audiences()->contacts()->list($grupoId);
            return $result['data'] ?? [];
        } catch (Exception $e) {
            Log::error('Erro ao obter contatos do grupo de audiência: ' . $e->getMessage());
            return [];
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
        if (!$this->enabled) {
            Log::info('Resend está desativado');
            return ['sucesso' => false, 'mensagem' => 'Serviço Resend desativado'];
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
            $result = Resend::emails()->send([
                'from' => $remetente,
                'to' => ["audience:{$grupoId}"],
                'subject' => $assunto,
                'html' => $template,
                'tags' => [
                    ['name' => 'oportunidade_id', 'value' => (string)$oportunidadeId],
                    ['name' => 'campanha', 'value' => 'marketing']
                ]
            ]);
            
            if ($result) {
                $resultado['enviados'] = $resultado['total_contatos'];
                $resultado['id_campanha'] = $result['id'] ?? null;
                Log::info("Campanha enviada para oportunidade {$oportunidadeId} com sucesso");
            } else {
                $resultado['sucesso'] = false;
                $resultado['falhas'] = $resultado['total_contatos'];
                Log::warning("Falha ao enviar campanha");
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
            foreach ($contatos as $contato) {
                if ($contato['email'] === $emailTeste) {
                    $encontrouContato = true;
                    break;
                }
            }
            $resultado['etapas'][] = [
                'etapa' => 'verificar_contato',
                'sucesso' => $encontrouContato,
                'total_contatos' => count($contatos)
            ];
            
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
}
