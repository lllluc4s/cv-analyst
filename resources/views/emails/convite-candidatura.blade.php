<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convite para Oportunidade</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .company-logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 20px;
        }
        .title {
            color: #2563eb;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .highlight {
            background-color: #dbeafe;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .btn:hover {
            background-color: #1d4ed8;
        }
        .skills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 15px 0;
        }
        .skill-tag {
            background-color: #e5e7eb;
            color: #374151;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
        }
        .personalized-message {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($company->logo_url)
                <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="company-logo">
            @endif
            <h1 class="title">Convite Especial para Oportunidade</h1>
        </div>

        <p>Olá <strong>{{ $candidato->nome }}</strong>,</p>

        <p>A empresa <strong>{{ $company->name }}</strong> identificou o seu perfil como sendo uma excelente combinação para a nossa oportunidade:</p>

        <div class="highlight">
            <h2 style="margin-top: 0; color: #1f2937;">{{ $oportunidade->titulo }}</h2>
            
            @if($oportunidade->localizacao)
                <p><strong>📍 Localização:</strong> {{ $oportunidade->localizacao }}</p>
            @endif
            
            <p>{{ $oportunidade->descricao }}</p>
            
            @if($oportunidade->skills_desejadas && count($oportunidade->skills_desejadas) > 0)
                <p><strong>Skills principais:</strong></p>
                <div class="skills">
                    @foreach(array_slice($oportunidade->skills_desejadas, 0, 8) as $skill)
                        <span class="skill-tag">
                            {{ is_array($skill) ? $skill['nome'] : $skill }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        @if($mensagemPersonalizada)
            <div class="personalized-message">
                <strong>Mensagem da empresa:</strong><br>
                {{ $mensagemPersonalizada }}
            </div>
        @endif

        <p>Acreditamos que as suas competências técnicas e experiência são ideais para esta posição. Gostaríamos de o convidar a candidatar-se!</p>

        <div style="text-align: center;">
            <a href="{{ $linkCandidatura }}" class="btn">
                🚀 Candidatar-me Agora
            </a>
        </div>

        <p><strong>Porquê foi selecionado?</strong></p>
        <ul>
            <li>O seu perfil de competências faz match com os nossos requisitos</li>
            <li>A sua experiência é relevante para esta posição</li>
            <li>Valorizamos o seu potencial para contribuir para a nossa equipa</li>
        </ul>

        <p>Este convite é válido por <strong>30 dias</strong>. Não perca esta oportunidade!</p>

        <div class="footer">
            <p>Este email foi enviado pela <strong>{{ $company->name }}</strong> através da plataforma CV Analyst.</p>
            <p>Se não deseja receber mais convites como este, pode gerir as suas preferências no seu perfil.</p>
            
            @if($company->website)
                <p>Saiba mais sobre a empresa: <a href="{{ $company->website }}">{{ $company->website }}</a></p>
            @endif
        </div>
    </div>
</body>
</html>
