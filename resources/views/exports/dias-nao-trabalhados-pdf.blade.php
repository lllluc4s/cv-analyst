<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Dias Não Trabalhados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #1f2937;
        }
        
        .header p {
            margin: 5px 0;
            color: #6b7280;
        }
        
        .filters {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .filters h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #374151;
        }
        
        .filters p {
            margin: 5px 0;
            font-size: 11px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th,
        .table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        .table th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 11px;
        }
        
        .table td {
            font-size: 10px;
        }
        
        .status {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        
        .status-pendente {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-aprovado {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-recusado {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Dias Não Trabalhados</h1>
        <p>Gerado em: {{ $dataGeracao }}</p>
    </div>

    @if(!empty($filtros))
    <div class="filters">
        <h3>Filtros Aplicados:</h3>
        @if(!empty($filtros['status']))
            <p><strong>Status:</strong> {{ $filtros['status'] }}</p>
        @endif
        @if(!empty($filtros['data_inicio']))
            <p><strong>Data Início:</strong> {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }}</p>
        @endif
        @if(!empty($filtros['data_fim']))
            <p><strong>Data Fim:</strong> {{ \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y') }}</p>
        @endif
    </div>
    @endif

    @if($dados->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 25%;">Nome do Colaborador</th>
                    <th style="width: 12%;">Data da Ausência</th>
                    <th style="width: 30%;">Motivo</th>
                    <th style="width: 10%;">Estado</th>
                    <th style="width: 8%;">Documento</th>
                    <th style="width: 15%;">Data do Pedido</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dados as $item)
                <tr>
                    <td>{{ $item->colaborador->nome_completo }}</td>
                    <td>{{ $item->data_ausencia->format('d/m/Y') }}</td>
                    <td>{{ $item->motivo }}</td>
                    <td>
                        @php
                            $statusClass = match($item->status) {
                                'pendente' => 'status-pendente',
                                'aprovado' => 'status-aprovado',
                                'recusado' => 'status-recusado',
                                default => 'status-pendente'
                            };
                            $statusLabel = match($item->status) {
                                'pendente' => 'Pendente',
                                'aprovado' => 'Aprovado',
                                'recusado' => 'Recusado',
                                default => 'Desconhecido'
                            };
                        @endphp
                        <span class="status {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td style="text-align: center;">{{ $item->documento_path ? 'Sim' : 'Não' }}</td>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>Nenhum registro encontrado com os filtros aplicados.</p>
        </div>
    @endif

    <div class="footer">
        <p>Este relatório contém {{ $dados->count() }} registro(s) de dias não trabalhados.</p>
    </div>
</body>
</html>
