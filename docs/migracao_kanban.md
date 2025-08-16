# Documentação da Migração do Kanban

## Visão Geral

Este documento detalha a migração e refatoração do sistema de Kanban para um novo padrão de tabelas, permitindo maior flexibilidade, melhor organização e suporte a funcionalidades adicionais. A migração foi concluída com sucesso, mantendo compatibilidade com o sistema existente.

## Tabelas Antigas vs. Novas Tabelas

### Antigas
- `board_states`: Estados do Kanban (colunas)
- `candidatura_history`: Histórico de movimentações
- Campo `notas_privadas` em `candidaturas`
- Campo `board_state_id` em `candidaturas`

### Novas
- `kanban_stages`: Estágios do Kanban por oportunidade
- `kanban_stage_settings`: Configurações de estágio (cores, emails, etc)
- `kanban_transitions`: Histórico de movimentações
- `kanban_notes`: Notas privadas
- Campo `stage_id` em `candidaturas`

## O que foi migrado

1. Todos os `board_states` foram migrados para `kanban_stages` e `kanban_stage_settings`
2. Todo o histórico de `candidatura_history` foi migrado para `kanban_transitions`
3. Todas as notas privadas das candidaturas foram migradas para `kanban_notes`
4. Todas as candidaturas agora têm o campo `stage_id` preenchido correspondente ao `board_state_id`

## Principais Melhorias

1. **Melhor organização**: Separação clara de responsabilidades entre tabelas
2. **Histórico mais detalhado**: Agora inclui estágio de origem e destino
3. **Notas privadas com histórico**: Agora as notas são registradas com timestamp e autor
4. **Configurações de estágio separadas**: Permite mais facilidade para ajustes e personalizações

## Compatibilidade

Para garantir compatibilidade com sistemas existentes:

1. Os campos antigos (`board_state_id`, `notas_privadas`) são mantidos e atualizados em paralelo
2. O novo controlador atualiza ambos os sistemas (novo e antigo)
3. A API mantém as mesmas rotas e respostas, permitindo que o frontend continue funcionando normalmente

## Alterações no Código

1. **Modelos**: Novos modelos foram criados para `KanbanStage`, `KanbanStageSetting`, `KanbanTransition` e `KanbanNote`
2. **Controladores**: O `KanbanBoardController` foi atualizado para usar as novas tabelas
3. **Rotas**: Mantidas as mesmas para compatibilidade

## Monitoramento e Diagnóstico

Foi criado um comando de diagnóstico para monitorar a migração:

```bash
php artisan kanban:diagnostico
```

Este comando verifica a consistência dos dados entre as tabelas antigas e novas.

## Próximos Passos

1. **Monitorar o funcionamento**: Verificar se o sistema está funcionando corretamente em produção
2. **Atualização do frontend**: Atualizar o frontend para usar diretamente os novos campos (opcional)
3. **Remoção das tabelas antigas**: Após período de estabilidade, considerar a remoção das tabelas antigas

## Contato

Para qualquer dúvida ou problema relacionado à migração, entre em contato com a equipe de desenvolvimento.

---

## Apêndice: Estrutura das Novas Tabelas

### kanban_stages
- `id`: ID único do estágio
- `name`: Nome do estágio
- `order`: Ordem de exibição no Kanban
- `is_default`: Se é um estágio padrão
- `company_id`: ID da empresa (opcional)
- `oportunidade_id`: ID da oportunidade (opcional)

### kanban_stage_settings
- `id`: ID único da configuração
- `stage_id`: ID do estágio relacionado
- `color`: Cor do estágio
- `email_enabled`: Se o envio de email está habilitado
- `email_subject`: Assunto do email
- `email_body`: Corpo do email

### kanban_transitions
- `id`: ID único da transição
- `candidatura_id`: ID da candidatura
- `from_stage_id`: ID do estágio de origem
- `to_stage_id`: ID do estágio de destino
- `note`: Nota opcional sobre a transição
- `email_sent`: Se um email foi enviado
- `email_data`: Dados do email enviado

### kanban_notes
- `id`: ID único da nota
- `candidatura_id`: ID da candidatura
- `oportunidade_id`: ID da oportunidade
- `content`: Conteúdo da nota
- `created_by`: ID da empresa que criou a nota
