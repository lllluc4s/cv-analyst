import api from './api';

export interface DiaNaoTrabalhado {
  id: number;
  colaborador_id: number;
  data_ausencia: string;
  motivo: string;
  documento_path?: string;
  status: 'pendente' | 'aprovado' | 'recusado';
  observacoes_empresa?: string;
  aprovado_em?: string;
  aprovado_por?: number;
  created_at: string;
  updated_at: string;
  colaborador?: {
    id: number;
    nome_completo: string;
    email_pessoal: string;
  };
  aprovado_por_user?: {
    id: number;
    name: string;
  };
}

export interface DiaNaoTrabalhadoForm {
  colaborador_id: number;
  data_ausencia: string;
  motivo: string;
  documento?: File;
}

export interface DiaNaoTrabalhadoResponse {
  success: boolean;
  data: DiaNaoTrabalhado | DiaNaoTrabalhado[];
  message?: string;
  errors?: any;
}

class DiasNaoTrabalhadosService {
  // Métodos para colaboradores
  async listarSolicitacoes(colaboradorId?: number): Promise<DiaNaoTrabalhadoResponse> {
    const params = colaboradorId ? { colaborador_id: colaboradorId } : {};
    const response = await api.get('/colaborador/dias-nao-trabalhados', { params });
    return response.data;
  }

  async criarSolicitacao(data: DiaNaoTrabalhadoForm): Promise<DiaNaoTrabalhadoResponse> {
    const formData = new FormData();
    formData.append('colaborador_id', data.colaborador_id.toString());
    formData.append('data_ausencia', data.data_ausencia);
    formData.append('motivo', data.motivo);
    
    if (data.documento) {
      formData.append('documento', data.documento);
    }

    const response = await api.post('/colaborador/dias-nao-trabalhados', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  async obterSolicitacao(id: number): Promise<DiaNaoTrabalhadoResponse> {
    const response = await api.get(`/colaborador/dias-nao-trabalhados/${id}`);
    return response.data;
  }

  async baixarDocumento(id: number): Promise<Blob> {
    const response = await api.get(`/colaborador/dias-nao-trabalhados/${id}/documento`, {
      responseType: 'blob',
    });
    return response.data;
  }

  // Métodos para empresas
  async listarSolicitacoesPorEmpresa(companyId?: number, status?: string): Promise<DiaNaoTrabalhadoResponse> {
    const params: any = {};
    if (status) params.status = status;
    
    const response = await api.get('/dias-nao-trabalhados/empresa/listar', { params });
    return response.data;
  }

  async aprovarSolicitacao(id: number, observacoes?: string): Promise<DiaNaoTrabalhadoResponse> {
    const response = await api.post(`/dias-nao-trabalhados/${id}/aprovar`, {
      observacoes_empresa: observacoes,
    });
    return response.data;
  }

  async recusarSolicitacao(id: number, observacoes?: string): Promise<DiaNaoTrabalhadoResponse> {
    const response = await api.post(`/dias-nao-trabalhados/${id}/recusar`, {
      observacoes_empresa: observacoes,
    });
    return response.data;
  }

  async obterEstatisticas(companyId: number): Promise<any> {
    const response = await api.get('/dias-nao-trabalhados/empresa/estatisticas', {
      params: { company_id: companyId },
    });
    return response.data;
  }

  // Métodos de exportação
  async exportarPdf(companyId: number, filtros?: any): Promise<Blob> {
    const params = { company_id: companyId, ...filtros };
    const response = await api.get('/dias-nao-trabalhados/empresa/exportar/pdf', {
      params,
      responseType: 'blob',
    });
    return response.data;
  }

  async exportarExcel(companyId: number, filtros?: any): Promise<Blob> {
    const params = { company_id: companyId, ...filtros };
    const response = await api.get('/dias-nao-trabalhados/empresa/exportar/excel', {
      params,
      responseType: 'blob',
    });
    return response.data;
  }

  // Helpers
  getStatusLabel(status: string): string {
    const labels = {
      'pendente': 'Pendente',
      'aprovado': 'Aprovado',
      'recusado': 'Recusado',
    };
    return labels[status as keyof typeof labels] || status;
  }

  getStatusColor(status: string): string {
    const colors = {
      'pendente': 'text-yellow-600 bg-yellow-100',
      'aprovado': 'text-green-600 bg-green-100',
      'recusado': 'text-red-600 bg-red-100',
    };
    return colors[status as keyof typeof colors] || 'text-gray-600 bg-gray-100';
  }
}

export default new DiasNaoTrabalhadosService();
