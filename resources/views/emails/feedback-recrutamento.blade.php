<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback sobre o Processo de Recrutamento</title>
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
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #667eea;
        }
        .header h1 {
            color: #667eea;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .congratulations {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 30px;
        }
        .congratulations h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .highlight {
            background-color: #f8f9fa;
            padding: 20px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .company-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .stars {
            color: #ffd700;
            font-size: 20px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Parabéns!</h1>
            <p>Você foi contratado!</p>
        </div>

        <div class="congratulations">
            <h2>Bem-vindo à {{ $company->name }}!</h2>
            <p>Ficamos muito felizes em tê-lo na nossa equipe para a posição de <strong>{{ $oportunidade->titulo }}</strong>.</p>
        </div>

        <div class="content">
            <p>Olá <strong>{{ $colaborador->nome_completo }}</strong>,</p>
            
            <p>É com grande prazer que confirmamos que você foi selecionado para fazer parte da nossa equipe! Este é um momento especial tanto para você quanto para nós.</p>

            <div class="company-info">
                <h3 style="margin-top: 0; color: #667eea;">📋 Detalhes da Posição:</h3>
                <p><strong>Cargo:</strong> {{ $oportunidade->titulo }}</p>
                <p><strong>Empresa:</strong> {{ $company->name }}</p>
                <p><strong>Data de Início:</strong> {{ $colaborador->data_inicio_contrato ? \Carbon\Carbon::parse($colaborador->data_inicio_contrato)->format('d/m/Y') : 'A definir' }}</p>
            </div>

            <div class="highlight">
                <h3 style="margin-top: 0; color: #667eea;">🔍 Ajude-nos a Melhorar!</h3>
                <p>A sua opinião é muito importante para nós. Gostaríamos de conhecer a sua experiência durante o processo de recrutamento para podermos continuar a melhorar.</p>
                
                <div class="stars">⭐⭐⭐⭐⭐</div>
                
                <p><strong>O questionário é rápido e anónimo, e incluí perguntas sobre:</strong></p>
                <ul>
                    <li>Como avalia o processo de recrutamento (1 a 5 estrelas)</li>
                    <li>O que mais gostou no processo</li>
                    <li>O que poderíamos ter melhorado</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $linkFeedback }}" class="btn">
                    📝 Dar o Meu Feedback
                </a>
            </div>

            <p><strong>Por que o seu feedback é importante?</strong></p>
            <ul>
                <li>🎯 Ajuda-nos a melhorar a experiência para futuros candidatos</li>
                <li>💪 Permite-nos identificar os pontos fortes do nosso processo</li>
                <li>🔧 Ajuda-nos a corrigir possíveis pontos de melhoria</li>
                <li>🤝 Demonstra o nosso compromisso com a excelência</li>
            </ul>

            <p>Mais uma vez, bem-vindo à equipa! Estamos ansiosos para começar esta jornada juntos.</p>
        </div>

        <div class="footer">
            <p>Este email foi enviado pela <strong>{{ $company->name }}</strong> através da plataforma CV Analyst.</p>
            @if($company->website)
                <p>Saiba mais sobre a empresa: <a href="{{ $company->website }}">{{ $company->website }}</a></p>
            @endif
            <p><small>O questionário de feedback é opcional e anónimo. O link é válido por 30 dias.</small></p>
        </div>
    </div>
</body>
</html>
