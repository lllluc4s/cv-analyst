<?php

namespace App\Services;

use App\Models\Colaborador;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;

class ContratoService
{
    /**
     * Gera um contrato de trabalho em formato DOCX
     */
    public function gerarContrato(Colaborador $colaborador): string
    {
        $phpWord = new PhpWord();
        
        // Configurar metadados
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('CV Analyst');
        $properties->setTitle('Contrato de Trabalho - ' . $colaborador->nome_completo);
        $properties->setDescription('Contrato de trabalho gerado automaticamente');

        // Criar seção
        $section = $phpWord->addSection();
        
        // Adicionar cabeçalho
        $this->adicionarCabecalho($section);
        
        // Adicionar conteúdo do contrato
        $this->adicionarConteudoContrato($section, $colaborador);
        
        // Adicionar assinaturas
        $this->adicionarAssinaturas($section);
        
        // Salvar arquivo
        $filename = $this->gerarNomeArquivo($colaborador);
        $filepath = storage_path('app/contratos/' . $filename);
        
        // Criar diretório se não existir
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filepath);
        
        return $filename;
    }
    
    private function adicionarCabecalho($section)
    {
        // Título centralizado
        $titleStyle = ['bold' => true, 'size' => 16, 'name' => 'Arial'];
        $centerParagraph = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceBefore' => 240];
        
        $section->addText('CONTRATO DE TRABALHO', $titleStyle, $centerParagraph);
        $section->addTextBreak(2);
    }
    
    private function adicionarConteudoContrato($section, Colaborador $colaborador)
    {
        $normalStyle = ['size' => 12, 'name' => 'Arial'];
        $boldStyle = ['size' => 12, 'name' => 'Arial', 'bold' => true];
        $paragraphStyle = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceBefore' => 120, 'spaceAfter' => 120];
        
        // Preâmbulo
        $section->addText(
            'Entre as partes a seguir identificadas:',
            $normalStyle,
            $paragraphStyle
        );
        
        $section->addTextBreak();
        
        // Informações da empresa
        $section->addText('OUTORGANTE EMPREGADOR:', $boldStyle, $paragraphStyle);
        $nomeEmpresa = $colaborador->company->nome ?? '[NOME DA EMPRESA]';
        $section->addText($nomeEmpresa, $normalStyle, $paragraphStyle);
        
        if ($colaborador->company && $colaborador->company->email) {
            $section->addText("Email: {$colaborador->company->email}", $normalStyle, $paragraphStyle);
        }
        
        $section->addTextBreak();
        
        // Informações do colaborador
        $section->addText('OUTORGADO TRABALHADOR:', $boldStyle, $paragraphStyle);
        
        $section->addText(
            "Nome: {$colaborador->nome_completo}",
            $normalStyle,
            $paragraphStyle
        );
        
        $section->addText(
            "Email: {$colaborador->email_pessoal}",
            $normalStyle,
            $paragraphStyle
        );
        
        // Adicionar telefone se disponível via candidatura
        if ($colaborador->candidatura && $colaborador->candidatura->telefone) {
            $section->addText(
                "Telefone: {$colaborador->candidatura->telefone}",
                $normalStyle,
                $paragraphStyle
            );
        }
        
        if ($colaborador->numero_contribuinte) {
            $section->addText(
                "Número de Contribuinte: {$colaborador->numero_contribuinte}",
                $normalStyle,
                $paragraphStyle
            );
        }
        
        if ($colaborador->numero_seguranca_social) {
            $section->addText(
                "Número de Segurança Social: {$colaborador->numero_seguranca_social}",
                $normalStyle,
                $paragraphStyle
            );
        }
        
        if ($colaborador->iban) {
            $section->addText(
                "IBAN: {$colaborador->iban}",
                $normalStyle,
                $paragraphStyle
            );
        }
        
        $section->addTextBreak();
        
        // Cláusulas do contrato
        $section->addText('É celebrado o presente contrato de trabalho, nas seguintes condições:', $normalStyle, $paragraphStyle);
        
        $section->addTextBreak();
        
        // Cláusula 1 - Função
        $section->addText('CLÁUSULA 1ª - FUNÇÃO', $boldStyle, $paragraphStyle);
        $funcao = $colaborador->posicao ?? 'A definir';
        $departamento = $colaborador->departamento ?? 'Geral';
        $section->addText(
            "O trabalhador exercerá a função de {$funcao} no departamento de {$departamento}.",
            $normalStyle,
            $paragraphStyle
        );
        
        $section->addTextBreak();
        
        // Cláusula 2 - Remuneração
        $section->addText('CLÁUSULA 2ª - REMUNERAÇÃO', $boldStyle, $paragraphStyle);
        $vencimento = $colaborador->vencimento ? number_format($colaborador->vencimento, 2, ',', '.') . '€' : 'A definir';
        $section->addText(
            "O trabalhador receberá uma remuneração mensal de {$vencimento}, paga até ao último dia útil de cada mês.",
            $normalStyle,
            $paragraphStyle
        );
        
        $section->addTextBreak();
        
        // Cláusula 3 - Duração
        $section->addText('CLÁUSULA 3ª - DURAÇÃO DO CONTRATO', $boldStyle, $paragraphStyle);
        $dataInicio = $colaborador->data_inicio_contrato ? $colaborador->data_inicio_contrato->format('d/m/Y') : 'A definir';
        $textoContrato = "O presente contrato terá início em {$dataInicio}";
        
        if ($colaborador->data_fim_contrato) {
            $dataFim = $colaborador->data_fim_contrato->format('d/m/Y');
            $textoContrato .= " e terminará em {$dataFim}.";
        } else {
            $textoContrato .= " e é celebrado por tempo indeterminado.";
        }
        
        $section->addText($textoContrato, $normalStyle, $paragraphStyle);
        
        $section->addTextBreak();
        
        // Cláusula 4 - Horário
        $section->addText('CLÁUSULA 4ª - HORÁRIO DE TRABALHO', $boldStyle, $paragraphStyle);
        $section->addText(
            'O horário de trabalho será de 8 horas diárias, de segunda a sexta-feira, conforme estabelecido pela empresa.',
            $normalStyle,
            $paragraphStyle
        );
        
        $section->addTextBreak();
        
        // Cláusula 5 - Disposições gerais
        $section->addText('CLÁUSULA 5ª - DISPOSIÇÕES GERAIS', $boldStyle, $paragraphStyle);
        $section->addText(
            'O presente contrato rege-se pelas disposições legais aplicáveis e pelas normas internas da empresa.',
            $normalStyle,
            $paragraphStyle
        );
        
        $section->addTextBreak(2);
    }
    
    private function adicionarAssinaturas($section)
    {
        $normalStyle = ['size' => 12, 'name' => 'Arial'];
        $paragraphStyle = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceBefore' => 240];
        
        $section->addText(
            'Lisboa, ' . now()->format('d \d\e F \d\e Y'),
            $normalStyle,
            $paragraphStyle
        );
        
        $section->addTextBreak(3);
        
        // Tabela para assinaturas
        $table = $section->addTable();
        $table->addRow();
        
        $cell1 = $table->addCell(4500);
        $cell1->addText('O Empregador', $normalStyle, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $cell1->addTextBreak(3);
        $cell1->addText('_______________________', $normalStyle, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        
        $cell2 = $table->addCell(4500);
        $cell2->addText('O Trabalhador', $normalStyle, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $cell2->addTextBreak(3);
        $cell2->addText('_______________________', $normalStyle, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
    }
    
    private function gerarNomeArquivo(Colaborador $colaborador): string
    {
        $nome = str_replace(' ', '_', strtolower($colaborador->nome_completo));
        $nome = preg_replace('/[^a-z0-9_]/', '', $nome);
        $data = now()->format('Y-m-d');
        
        return "contrato_{$nome}_{$data}.docx";
    }
    
    /**
     * Retorna o caminho completo do arquivo de contrato
     */
    public function getCaminhoContrato(string $filename): string
    {
        return storage_path('app/contratos/' . $filename);
    }
}
