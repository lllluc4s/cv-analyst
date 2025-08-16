import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8001'
const API_BASE = `${API_URL}/api`

export interface ColaboradorData {
  id: number
  empresa: {
    id: number
    nome: string
    logo_url: string | null
  }
  posicao: string
  departamento: string
  vencimento: number | null
  data_inicio_contrato: string
  data_fim_contrato: string | null
  contrato_ativo: boolean
  sem_termo: boolean
  oportunidade_origem: {
    id: number
    titulo: string
    slug: string
  }
  candidatura_data?: string
}

export interface ColaboradorProfile {
  candidato: {
    id: number
    nome: string
    apelido: string
    email: string
    nome_completo: string
  }
  colaboradores: ColaboradorData[]
}

export interface ColaboradorDashboard {
  estatisticas: {
    total_candidaturas: number
    candidaturas_contratadas: number
    taxa_sucesso: number
    contratos_ativos: number
  }
  contratos_ativos: Array<{
    id: number
    empresa: string
    posicao: string
    departamento: string
    data_inicio: string
    data_fim: string | null
    contrato_ativo: boolean
  }>
  historico_contratos: Array<{
    id: number
    empresa: string
    posicao: string
    departamento: string
    data_inicio: string
    data_fim: string | null
    contrato_ativo: boolean
  }>
}

class ColaboradorService {
  private static readonly TOKEN_KEY = 'candidato_token'

  private getAuthHeaders() {
    const token = localStorage.getItem(ColaboradorService.TOKEN_KEY)
    return token ? { Authorization: `Bearer ${token}` } : {}
  }

  async verificarAcesso(): Promise<{ is_colaborador: boolean; message: string }> {
    const response = await axios.get(`${API_BASE}/candidatos/verificar-acesso-colaborador`, {
      headers: this.getAuthHeaders()
    })
    return response.data
  }

  async getProfile(): Promise<ColaboradorProfile> {
    const response = await axios.get(`${API_BASE}/colaborador/me`, {
      headers: this.getAuthHeaders()
    })
    return response.data
  }

  async getDashboard(): Promise<ColaboradorDashboard> {
    const response = await axios.get(`${API_BASE}/colaborador/dashboard`, {
      headers: this.getAuthHeaders()
    })
    return response.data
  }

  async getHistoricoContratos(): Promise<ColaboradorData[]> {
    const response = await axios.get(`${API_BASE}/colaborador/historico-contratos`, {
      headers: this.getAuthHeaders()
    })
    return response.data.contratos || response.data
  }

  // Métodos auxiliares
  hasToken(): boolean {
    return !!localStorage.getItem(ColaboradorService.TOKEN_KEY)
  }

  getToken(): string | null {
    return localStorage.getItem(ColaboradorService.TOKEN_KEY)
  }

  setToken(token: string): void {
    localStorage.setItem(ColaboradorService.TOKEN_KEY, token)
  }

  removeToken(): void {
    localStorage.removeItem(ColaboradorService.TOKEN_KEY)
  }

  // Método para logout
  async logout(): Promise<void> {
    try {
      await axios.post(`${API_BASE}/candidatos/logout`, {}, {
        headers: this.getAuthHeaders()
      })
    } catch (error) {
      console.error('Erro ao fazer logout:', error)
    } finally {
      this.removeToken()
    }
  }

  // Método para verificar se o token é válido
  async isTokenValid(): Promise<boolean> {
    try {
      await axios.get(`${API_BASE}/candidatos/me`, {
        headers: this.getAuthHeaders()
      })
      return true
    } catch {
      return false
    }
  }

  // Método utilitário para formatação de datas
  formatDate(dateString: string): string {
    if (!dateString) return ''
    
    try {
      const date = new Date(dateString)
      return date.toLocaleDateString('pt-BR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    } catch (error) {
      return dateString
    }
  }

  // Método utilitário para formatação de moeda
  formatCurrency(value: number | null): string {
    if (value === null || value === undefined) {
      return 'Não informado'
    }
    
    try {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(value)
    } catch (error) {
      return `€ ${value.toFixed(2)}`
    }
  }
}

export default new ColaboradorService()
