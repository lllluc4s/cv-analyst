<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Newsletter CV Analyst</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
            line-height: 1.6; 
            color: #374151; 
            margin: 0; 
            padding: 0; 
            background-color: #f9fafb;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header { 
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); 
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .header p {
            margin: 8px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content { 
            padding: 30px 20px; 
        }
        .welcome {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f0f9ff;
            border-left: 4px solid #2563eb;
            border-radius: 6px;
        }
        .welcome h2 {
            margin: 0 0 10px 0;
            color: #1e40af;
            font-size: 20px;
        }
        .stats {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        .stats-number {
            font-size: 32px;
            font-weight: 700;
            color: #2563eb;
            margin: 0;
        }
        .stats-label {
            font-size: 14px;
            color: #6b7280;
            margin: 5px 0 0 0;
        }
        .oportunidade {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .oportunidade:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .oportunidade-header {
            padding: 20px;
            background-color: white;
        }
        .oportunidade-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 8px 0;
            text-decoration: none;
        }
        .oportunidade-title:hover {
            color: #2563eb;
        }
        .company-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .company-logo {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #6b7280;
            flex-shrink: 0;
        }
        .company-name {
            font-weight: 500;
            color: #4b5563;
        }
        .oportunidade-meta {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        .oportunidade-description {
            color: #374151;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        .skills-container {
            margin: 15px 0;
        }
        .skills-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }
        .skills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .skill {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .btn-candidatar {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: transform 0.2s;
        }
        .btn-candidatar:hover {
            transform: translateY(-1px);
            color: white;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #6b7280;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
        .unsubscribe {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 20px;
        }
        .unsubscribe a {
            color: #6b7280;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 0;
                box-shadow: none;
            }
            .header, .content, .footer {
                padding-left: 15px;
                padding-right: 15px;
            }
            .company-info {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>CV Analyst</h1>
            <p>Newsletter Semanal de Oportunidades</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <!-- Welcome Section -->
            <div class="welcome">
                <h2>Olá {{ $nomeCandidato }}!</h2>
                <p>Preparámos para si uma seleção das melhores oportunidades de emprego publicadas esta semana. Não perca estas oportunidades!</p>
            </div>
            
            <!-- Stats -->
            <div class="stats">
                <p class="stats-number">{{ $dadosNewsletter['total_oportunidades'] }}</p>
                <p class="stats-label">Novas Oportunidades Esta Semana</p>
            </div>
            
            <!-- Oportunidades -->
            @foreach($dadosNewsletter['oportunidades'] as $oportunidade)
            <div class="oportunidade">
                <div class="oportunidade-header">
                    <div class="company-info">
                        <div class="company-logo">
                            @if($oportunidade->company && $oportunidade->company->logo_url)
                                <img src="{{ $oportunidade->company->logo_url }}" alt="{{ $oportunidade->company->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
                            @else
                                {{ substr($oportunidade->company->name ?? 'CV', 0, 2) }}
                            @endif
                        </div>
                        <div>
                            <div class="company-name">{{ $oportunidade->company->name ?? 'Empresa' }}</div>
                            @if($oportunidade->localizacao)
                                <div style="font-size: 12px; color: #9ca3af;">📍 {{ $oportunidade->localizacao }}</div>
                            @endif
                        </div>
                    </div>
                    
                    <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/oportunidade/{{ $oportunidade->slug }}" class="oportunidade-title">
                        {{ $oportunidade->titulo }}
                    </a>
                    
                    <div class="oportunidade-meta">
                        Publicada em {{ $oportunidade->created_at->format('d/m/Y') }}
                    </div>
                    
                    @if($oportunidade->descricao)
                        <div class="oportunidade-description">
                            {{ Str::limit(strip_tags($oportunidade->descricao), 150) }}
                        </div>
                    @endif
                    
                    @if($oportunidade->skills_desejadas && count($oportunidade->skills_desejadas) > 0)
                        <div class="skills-container">
                            <div class="skills-label">Skills Desejadas</div>
                            <div class="skills">
                                @foreach(array_slice($oportunidade->skills_desejadas, 0, 5) as $skill)
                                    <span class="skill">{{ is_string($skill) ? $skill : $skill['nome'] ?? $skill }}</span>
                                @endforeach
                                @if(count($oportunidade->skills_desejadas) > 5)
                                    <span class="skill">+{{ count($oportunidade->skills_desejadas) - 5 }} mais</span>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <div style="margin-top: 20px;">
                        <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/oportunidade/{{ $oportunidade->slug }}" class="btn-candidatar">
                            Ver Oportunidade & Candidatar-se
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
            
            <!-- CTA Section -->
            <div style="text-align: center; margin-top: 40px; padding: 30px 20px; background-color: #f8fafc; border-radius: 8px;">
                <h3 style="margin: 0 0 15px 0; color: #111827;">Não encontrou a oportunidade ideal?</h3>
                <p style="margin: 0 0 20px 0; color: #6b7280;">Explore todas as oportunidades disponíveis na nossa plataforma.</p>
                <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/oportunidades" style="display: inline-block; background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Ver Todas as Oportunidades
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>CV Analyst</strong></p>
            <p>A sua plataforma de análise de currículos e oportunidades de emprego</p>
            <p>
                <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}">Visitar Website</a> | 
                <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/cv-analysis">Analisar CV</a>
            </p>
            
            <div class="unsubscribe">
                <p>Está a receber este email porque se candidatou a uma oportunidade na nossa plataforma.</p>
                <p>Newsletter enviada em {{ $dadosNewsletter['data_envio'] }}</p>
            </div>
        </div>
    </div>
</body>
</html>
