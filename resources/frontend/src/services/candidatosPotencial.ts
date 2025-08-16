import axios from 'axios'

export interface CandidatoPotencial {
  id: number
  nome: string
  email: string
  skills: string[]
  skills_principais: Array<{
    nome: string
    peso: number
  }>
  afinidade_percentual: number
  cv_path?: string
  linkedin_url?: string
}

export interface ConviteHistorico {
  id: number
  candidato: {
    nome: string
    email: string
  }
  enviado_em: string
  visualizado_em?: string
  candidatou_se: boolean
}

export interface CandidatosPotencialResponse {
  oportunidade: {
    id: number
    titulo: string
    skills_desejadas: Array<{ nome: string; peso: number } | string>
  }
  candidatos: CandidatoPotencial[]
}

export interface ConvitesHistoricoResponse {
  oportunidade: {
    id: number
    titulo: string
  }
  convites: ConviteHistorico[]
}

class CandidatosPotencialService {
  private baseURL = '/api/companies'

  /**
   * Buscar candidatos com potencial para uma oportunidade
   */
  async buscarCandidatosPotencial(oportunidadeId: number): Promise<CandidatosPotencialResponse> {
    const response = await axios.get(`${this.baseURL}/oportunidades/${oportunidadeId}/candidatos-potencial`)
    return response.data
  }

  /**
   * Convidar candidato para oportunidade
   */
  async convidarCandidato(data: {
    candidato_id: number
    oportunidade_id: number
    mensagem_personalizada?: string
  }): Promise<any> {
    const response = await axios.post(`${this.baseURL}/convites/candidatos`, data)
    return response.data
  }

  /**
   * Obter histórico de convites de uma oportunidade
   */
  async historicoConvites(oportunidadeId: number): Promise<ConvitesHistoricoResponse> {
    const response = await axios.get(`${this.baseURL}/oportunidades/${oportunidadeId}/convites`)
    return response.data
  }
}

export const candidatosPotencialService = new CandidatosPotencialService()
