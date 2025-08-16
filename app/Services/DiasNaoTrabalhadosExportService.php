<?php

namespace App\Services;

use App\Models\DiaNaoTrabalhado;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class DiasNaoTrabalhadosExportService
{
    /**
     * Exportar para PDF
     */
    public function exportarPdf(int $companyId, array $filtros = []): string
    {
        $dados = $this->obterDados($companyId, $filtros);
        
        $pdf = Pdf::loadView('exports.dias-nao-trabalhados-pdf', [
            'dados' => $dados,
            'filtros' => $filtros,
            'dataGeracao' => now()->format('d/m/Y H:i:s')
        ]);
        
        $filename = 'dias-nao-trabalhados-' . date('Y-m-d-H-i-s') . '.pdf';
        $filepath = storage_path('app/temp/' . $filename);
        
        // Criar diretório se não existir
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $pdf->save($filepath);
        
        return $filepath;
    }

    /**
     * Exportar para Excel
     */
    public function exportarExcel(int $companyId, array $filtros = []): string
    {
        $dados = $this->obterDados($companyId, $filtros);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título do documento
        $sheet->setCellValue('A1', 'Relatório de Dias Não Trabalhados');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Data de geração
        $sheet->setCellValue('A2', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Cabeçalhos
        $row = 4;
        $headers = [
            'A' => 'Nome do Colaborador',
            'B' => 'Data da Ausência',
            'C' => 'Motivo',
            'D' => 'Estado do Pedido',
            'E' => 'Documento',
            'F' => 'Data do Pedido'
        ];
        
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
        }
        
        // Estilo dos cabeçalhos
        $headerRange = 'A' . $row . ':F' . $row;
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E5E7EB');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        
        // Dados
        $row++;
        foreach ($dados as $item) {
            $sheet->setCellValue('A' . $row, $item->colaborador->nome_completo);
            $sheet->setCellValue('B' . $row, $item->data_ausencia->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $item->motivo);
            $sheet->setCellValue('D' . $row, $this->getStatusLabel($item->status));
            $sheet->setCellValue('E' . $row, $item->documento_path ? 'Sim' : 'Não');
            $sheet->setCellValue('F' . $row, $item->created_at->format('d/m/Y H:i'));
            $row++;
        }
        
        // Ajustar largura das colunas
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Aplicar bordas aos dados
        if ($row > 5) {
            $dataRange = 'A5:F' . ($row - 1);
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        }
        
        $filename = 'dias-nao-trabalhados-' . date('Y-m-d-H-i-s') . '.xlsx';
        $filepath = storage_path('app/temp/' . $filename);
        
        // Criar diretório se não existir
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filepath;
    }

    /**
     * Obter dados filtrados
     */
    private function obterDados(int $companyId, array $filtros = []): Collection
    {
        $query = DiaNaoTrabalhado::with(['colaborador', 'aprovadoPor'])
            ->whereHas('colaborador', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });

        // Aplicar filtros
        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        if (!empty($filtros['data_inicio'])) {
            $query->whereDate('data_ausencia', '>=', $filtros['data_inicio']);
        }

        if (!empty($filtros['data_fim'])) {
            $query->whereDate('data_ausencia', '<=', $filtros['data_fim']);
        }

        if (!empty($filtros['colaborador_id'])) {
            $query->where('colaborador_id', $filtros['colaborador_id']);
        }

        return $query->orderBy('data_ausencia', 'desc')->get();
    }

    /**
     * Obter label do status
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            DiaNaoTrabalhado::STATUS_PENDENTE => 'Pendente',
            DiaNaoTrabalhado::STATUS_APROVADO => 'Aprovado',
            DiaNaoTrabalhado::STATUS_RECUSADO => 'Recusado',
            default => 'Desconhecido',
        };
    }
}
