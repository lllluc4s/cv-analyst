<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CV - {{ $dados_pessoais['nome'] ?? 'Candidato' }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        .header { background: #2c3e50; color: white; padding: 20px; margin-bottom: 20px; }
        .header h1 { margin: 0 0 10px 0; font-size: 24px; }
        .contact { font-size: 12px; }
        .section { margin-bottom: 20px; }
        .section-title { background: #3498db; color: white; padding: 8px; font-weight: bold; margin-bottom: 10px; }
        .item { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .item:last-child { border-bottom: none; }
        .job-title { font-weight: bold; color: #2c3e50; }
        .company { color: #3498db; }
        .period { font-size: 11px; color: #666; }
        .skills { display: flex; flex-wrap: wrap; gap: 5px; }
        .skill { background: #3498db; color: white; padding: 3px 8px; border-radius: 10px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $dados_pessoais['nome'] ?? 'Nome do Candidato' }}</h1>
        <div class="contact">
            {{ $dados_pessoais['email'] ?? '' }} | 
            {{ $dados_pessoais['telefone'] ?? '' }} | 
            {{ $dados_pessoais['linkedin'] ?? '' }}
        </div>
    </div>

    {{-- Seção explícita para facilitar extração por IA --}}
    <div class="section">
        <div class="section-title">DADOS PESSOAIS</div>
        <p>
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
        <div class="section-title">RESUMO PROFISSIONAL</div>
        <p>{{ $resumo_pessoal }}</p>
    </div>
    @endif

    @if(!empty($experiencias))
    <div class="section">
        <div class="section-title">EXPERIÊNCIA PROFISSIONAL</div>
        @foreach($experiencias as $exp)
        <div class="item">
            <div class="job-title">{{ $exp['cargo'] ?? 'Cargo' }}</div>
            <div class="company">{{ $exp['empresa'] ?? 'Empresa' }}</div>
            <div class="period">{{ $exp['periodo'] ?? 'Período' }}</div>
            @if(!empty($exp['descricao']))
            <p>{{ $exp['descricao'] }}</p>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    @if(!empty($skills))
    <div class="section">
        <div class="section-title">COMPETÊNCIAS</div>
        {{-- Linha plana com lista de skills para facilitar varredura por texto --}}
        @php
            $flatSkills = [];
            foreach ($skills as $group) {
                if (!empty($group['habilidades']) && is_array($group['habilidades'])) {
                    foreach ($group['habilidades'] as $s) { $flatSkills[] = (string)$s; }
                }
            }
        @endphp
        @if(!empty($flatSkills))
            <p style="font-size: 11px; color:#555">Skills: {{ implode(', ', $flatSkills) }}</p>
        @endif
        @foreach($skills as $skillGroup)
        <div style="margin-bottom: 10px;">
            <strong>{{ $skillGroup['categoria'] ?? 'Competências' }}:</strong>
            <div class="skills">
                @if(!empty($skillGroup['habilidades']))
                    @foreach($skillGroup['habilidades'] as $skill)
                    <span class="skill">{{ $skill }}</span>
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
            <div class="job-title">{{ $form['curso'] ?? 'Curso' }}</div>
            <div class="company">{{ $form['instituicao'] ?? 'Instituição' }}</div>
            <div class="period">{{ $form['periodo'] ?? 'Período' }}</div>
        </div>
        @endforeach
    </div>
    @endif
</body>
</html>
