<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nova Candidatura</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4f46e5; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9fafb; }
        .info { margin: 10px 0; }
        .label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Nova Candidatura Recebida</h1>
        </div>
        <div class="content">
            <p>Uma nova candidatura foi recebida para a vaga:</p>
            
            <div class="info">
                <span class="label">Vaga:</span> {{ $candidatura->oportunidade->titulo }}
            </div>
            
            <div class="info">
                <span class="label">Candidato:</span> {{ $candidatura->nome }} {{ $candidatura->apelido }}
            </div>
            
            <div class="info">
                <span class="label">Email:</span> {{ $candidatura->email }}
            </div>
            
            <div class="info">
                <span class="label">Telefone:</span> {{ $candidatura->telefone }}
            </div>
            
            @if($candidatura->linkedin)
            <div class="info">
                <span class="label">LinkedIn:</span> <a href="{{ $candidatura->linkedin }}">{{ $candidatura->linkedin }}</a>
            </div>
            @endif
            
            <div class="info">
                <span class="label">Data da Candidatura:</span> {{ $candidatura->created_at->format('d/m/Y H:i') }}
            </div>
            
            <p style="margin-top: 20px;">
                <a href="{{ config('app.url') }}/candidaturas" style="background-color: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    Ver Candidaturas
                </a>
            </p>
        </div>
    </div>
</body>
</html>
