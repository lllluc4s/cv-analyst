import axios from 'axios'

export interface FeedbackEmpresa {
  id: number
  avaliacao_processo: number
  gostou_mais: string | null
  poderia_melhorar: string | null
  respondido_em: string
  colaborador: {
    id: number
    nome_completo: string
  }
}

export interface FeedbackStats {
  total_feedbacks: number
  avaliacao_media: number
  distribuicao_avaliacoes: {
    '1': number
    '2': number
    '3': number
    '4': number
    '5': number
  }
}

export interface FeedbacksOportunidadeResponse {
  oportunidade: any
  feedbacks: FeedbackEmpresa[]
  stats: FeedbackStats
}

class FeedbackEmpresaService {
  private baseURL = '/api/companies'

  /**
   * Listar feedbacks de uma oportunidade
   */
  async getFeedbacksOportunidade(oportunidadeId: number): Promise<FeedbacksOportunidadeResponse> {
    const response = await axios.get(`${this.baseURL}/oportunidades/${oportunidadeId}/feedbacks`)
    return response.data
  }
}

export const feedbackEmpresaService = new FeedbackEmpresaService()
