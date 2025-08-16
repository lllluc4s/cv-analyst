<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CV - {{ $dados_pessoais['nome'] ?? 'Candidato' }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; margin: 15px; color: #2c3e50; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .container { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .header { text-align: center; border-bottom: 3px solid #e74c3c; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #e74c3c; font-size: 28px; margin: 0; }
        .header .subtitle { color: #7f8c8d; font-style: italic; margin-top: 5px; }
        .section { margin-bottom: 20px; }
        .section-title { color: #e74c3c; font-size: 14px; font-weight: bold; border-bottom: 2px solid #e74c3c; padding-bottom: 5px; margin-bottom: 10px; }
        .item { margin-bottom: 12px; }
        .highlight { color: #e74c3c; font-weight: bold; }
        .skills-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px; }
        .skill-item { background: linear-gradient(45deg, #e74c3c, #f39c12); color: white; padding: 5px; text-align: center; border-radius: 15px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $dados_pessoais['nome'] ?? 'Nome do Candidato' }}</h1>
            <div class="subtitle">{{ $dados_pessoais['cargo'] ?? 'Profissional Criativo' }}</div>
            <div style="margin-top: 10px; font-size: 12px;">
                {{ $dados_pessoais['email'] ?? '' }} • {{ $dados_pessoais['telefone'] ?? '' }}
            </div>
        </div>

        {{-- Seção explícita para facilitar extração por IA --}}
        <div class="section">
            <div class="section-title">DADOS PESSOAIS</div>
            <p style="font-size: 12px;">
                Nome completo: {{ $dados_pessoais['nome'] ?? 'Não informado' }}<br>
                Email: {{ $dados_pessoais['email'] ?? 'Não informado' }}<br>
                Telefone: {{ $dados_pessoais['telefone'] ?? 'Não informado' }}<br>
                LinkedIn: {{-- garantir protocolo para melhor detecção --}}
                @php
                    $linkedin = $dados_pessoais['linkedin'] ?? '';
                    if ($linkedin && !preg_match('/^https?:\/\//i', $linkedin)) {
                        $linkedin = 'https://' . ltrim($linkedin, '/');
                    }
                @endphp
                {{ $linkedin ?: 'Não informado' }}
            </p>
        </div>

        @if(!empty($resumo_pessoal))
        <div class="section">
            <div class="section-title">PERFIL PROFISSIONAL</div>
            <p style="text-align: justify; line-height: 1.5;">{{ $resumo_pessoal }}</p>
        </div>
        @endif

        @if(!empty($experiencias))
        <div class="section">
            <div class="section-title">EXPERIÊNCIA</div>
            @foreach($experiencias as $exp)
            <div class="item">
                <div class="highlight">{{ $exp['cargo'] ?? 'Cargo' }}</div>
                <div>{{ $exp['empresa'] ?? 'Empresa' }} • {{ $exp['periodo'] ?? 'Período' }}</div>
                @if(!empty($exp['descricao']))
                <p style="margin-top: 5px; font-size: 11px; line-height: 1.4;">{{ $exp['descricao'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        @if(!empty($skills))
        <div class="section">
            <div class="section-title">COMPETÊNCIAS</div>
            @foreach($skills as $skillGroup)
            <div style="margin-bottom: 15px;">
                <div class="highlight">{{ $skillGroup['categoria'] ?? 'Competências' }}</div>
                <div class="skills-grid">
                    @if(!empty($skillGroup['habilidades']))
                        @foreach($skillGroup['habilidades'] as $skill)
                        <div class="skill-item">{{ $skill }}</div>
                        @endforeach
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if(!empty($formacao))
        <div class="section">
            <div class="section-title">FORMAÇÃO</div>
            @foreach($formacao as $form)
            <div class="item">
                <div class="highlight">{{ $form['curso'] ?? 'Curso' }}</div>
                <div>{{ $form['instituicao'] ?? 'Instituição' }} • {{ $form['periodo'] ?? 'Período' }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</body>
</html>
