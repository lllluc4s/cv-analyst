import axios from 'axios'

export interface FeedbackForm {
  avaliacao_processo: number
  gostou_mais: string
  poderia_melhorar: string
}

export interface FeedbackResponse {
  feedback: any
  oportunidade: any
  company: any
  colaborador?: any
}

export interface SubmitFeedbackResponse {
  message: string
  feedback: any
}

class FeedbackRecrutamentoService {
  private baseURL = '/api/feedback-recrutamento'

  /**
   * Obter dados do feedback pelo token
   */
  async getFeedback(token: string): Promise<FeedbackResponse> {
    const response = await axios.get(`${this.baseURL}/${token}`)
    return response.data
  }

  /**
   * Submeter feedback
   */
  async submitFeedback(token: string, data: FeedbackForm): Promise<SubmitFeedbackResponse> {
    const response = await axios.post(`${this.baseURL}/${token}`, data)
    return response.data
  }
}

export const feedbackRecrutamentoService = new FeedbackRecrutamentoService()
