<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV - {{ $dados_pessoais['nome'] ?? 'Candidato' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.4;
            font-size: 12px;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 25px;
            margin: -20px -20px 25px -20px;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 300;
        }
        
        .header .subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
        .contact-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            font-size: 11px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            background: #3498db;
            color: white;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            border-radius: 3px;
        }
        
        .resumo {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #3498db;
            font-style: italic;
            margin-bottom: 20px;
        }
        
        .experiencia-item {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        
        .experiencia-item:last-child {
            border-bottom: none;
        }
        
        .experiencia-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        
        .cargo {
            font-weight: bold;
            font-size: 13px;
            color: #2c3e50;
        }
        
        .empresa {
            color: #3498db;
            font-weight: 500;
            margin-bottom: 3px;
        }
        
        .periodo {
            font-size: 10px;
            color: #666;
            background: #f1f2f6;
            padding: 3px 8px;
            border-radius: 10px;
        }
        
        .descricao {
            margin-top: 8px;
            line-height: 1.5;
        }
        
        .conquistas {
            margin-top: 8px;
        }
        
        .conquistas ul {
            margin-left: 15px;
        }
        
        .conquistas li {
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .skills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .skill-category {
            flex: 1;
            min-width: 200px;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
        }
        
        .skill-category h4 {
            color: #3498db;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .skill-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .skill-tag {
            background: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
        }
        
        .formacao-item {
            margin-bottom: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .curso {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .instituicao {
            color: #3498db;
            font-size: 11px;
        }
        
        .dois-colunas {
            display: flex;
            gap: 25px;
        }
        
        .coluna-esquerda {
            flex: 2;
        }
        
        .coluna-direita {
            flex: 1;
        }
        
        @page {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $dados_pessoais['nome'] ?? 'Nome do Candidato' }}</h1>
            <div class="subtitle">{{ $dados_pessoais['cargo'] ?? 'Profissional' }}</div>
            <div class="contact-info">
                @if(!empty($dados_pessoais['email']))
                <div class="contact-item">
                    <span>✉</span> {{ $dados_pessoais['email'] }}
                </div>
                @endif
                @if(!empty($dados_pessoais['telefone']))
                <div class="contact-item">
                    <span>📞</span> {{ $dados_pessoais['telefone'] }}
                </div>
                @endif
                @if(!empty($dados_pessoais['linkedin']))
                <div class="contact-item">
                    <span>🔗</span> {{ $dados_pessoais['linkedin'] }}
                </div>
                @endif
                @if(!empty($dados_pessoais['localizacao']))
                <div class="contact-item">
                    <span>📍</span> {{ $dados_pessoais['localizacao'] }}
                </div>
                @endif
            </div>
        </div>

        @if(!empty($resumo_pessoal))
        <div class="resumo">
            {{ $resumo_pessoal }}
        </div>
        @endif

        <div class="dois-colunas">
            <div class="coluna-esquerda">
                @if(!empty($experiencias) && count($experiencias) > 0)
                <div class="section">
                    <div class="section-title">EXPERIÊNCIA PROFISSIONAL</div>
                    @foreach($experiencias as $exp)
                    <div class="experiencia-item">
                        <div class="experiencia-header">
                            <div>
                                <div class="cargo">{{ $exp['cargo'] ?? 'Cargo não informado' }}</div>
                                <div class="empresa">{{ $exp['empresa'] ?? 'Empresa não informada' }}</div>
                            </div>
                            <div class="periodo">{{ $exp['periodo'] ?? 'Período não informado' }}</div>
                        </div>
                        @if(!empty($exp['descricao']))
                        <div class="descricao">{{ $exp['descricao'] }}</div>
                        @endif
                        @if(!empty($exp['conquistas']) && is_array($exp['conquistas']))
                        <div class="conquistas">
                            <ul>
                                @foreach($exp['conquistas'] as $conquista)
                                <li>{{ $conquista }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            
            <div class="coluna-direita">
                @if(!empty($skills) && count($skills) > 0)
                <div class="section">
                    <div class="section-title">COMPETÊNCIAS</div>
                    @foreach($skills as $skillGroup)
                    <div class="skill-category">
                        <h4>{{ $skillGroup['categoria'] ?? 'Competências' }}</h4>
                        <div class="skill-tags">
                            @if(!empty($skillGroup['habilidades']) && is_array($skillGroup['habilidades']))
                                @foreach($skillGroup['habilidades'] as $skill)
                                <span class="skill-tag">{{ $skill }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @if(!empty($formacao) && count($formacao) > 0)
                <div class="section">
                    <div class="section-title">FORMAÇÃO</div>
                    @foreach($formacao as $form)
                    <div class="formacao-item">
                        <div class="curso">{{ $form['curso'] ?? 'Curso não informado' }}</div>
                        <div class="instituicao">{{ $form['instituicao'] ?? 'Instituição não informada' }}</div>
                        @if(!empty($form['periodo']))
                        <div class="periodo">{{ $form['periodo'] }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
