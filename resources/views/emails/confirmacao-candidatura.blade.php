<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Candidatura Confirmada</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #10b981; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9fafb; }
        .info { margin: 10px 0; }
        .label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Candidatura Confirmada</h1>
        </div>
        <div class="content">
            <p>Olá {{ $candidatura->nome }},</p>
            
            <p>Sua candidatura foi recebida com sucesso para a vaga:</p>
            
            <div class="info">
                <span class="label">Vaga:</span> {{ $candidatura->oportunidade->titulo }}
            </div>
            
            <div class="info">
                <span class="label">Data da Candidatura:</span> {{ $candidatura->created_at->format('d/m/Y H:i') }}
            </div>
            
            <p>Nossa equipe irá analisar seu perfil e retornará o contato em breve.</p>
            
            <p>Obrigado pelo interesse em fazer parte da nossa equipe!</p>
            
            <p style="margin-top: 30px;">
                Atenciosamente,<br>
                <strong>Equipe CV Analyst</strong>
            </p>
        </div>
    </div>
</body>
</html>
