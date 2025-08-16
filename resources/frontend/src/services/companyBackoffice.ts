import axios from 'axios'

const API_URL = `${import.meta.env.VITE_API_URL || 'http://localhost:8000/api'}/api`

export interface Oportunidade {
  id: number
  titulo: string
  descricao: string
  skills_desejadas: string[]
  localizacao?: string
  slug: string
  ativa: boolean
  publica: boolean
  company_id: number
  candidaturas_count?: number
  created_at: string
  updated_at: string
}

export interface CreateOportunidadeData {
  titulo: string
  descricao: string
  skills_desejadas: string[]
  localizacao?: string
  ativa?: boolean
  publica?: boolean
}

export interface UpdateOportunidadeData {
  titulo?: string
  descricao?: string
  skills_desejadas?: string[]
  localizacao?: string
  ativa?: boolean
  publica?: boolean
}

export interface Candidatura {
  id: number
  nome: string
  email: string
  cv_path: string
  skills: string[]
  analysis_result: any
  score: number
  created_at: string
  slug: string
}

export interface DashboardStats {
  total_oportunidades: number
  oportunidades_ativas: number
  total_candidaturas: number
}

export interface DashboardData {
  stats: DashboardStats
  recent_candidaturas: Array<{
    id: number
    nome: string
    email: string
    oportunidade_titulo: string
    score: number
    created_at: string
  }>
}

class CompanyBackofficeService {
  async getDashboard(): Promise<DashboardData> {
    const response = await axios.get(`${API_URL}/companies/dashboard`)
    return response.data
  }

  async getOportunidades(): Promise<Oportunidade[]> {
    const response = await axios.get(`${API_URL}/companies/oportunidades`)
    return response.data.oportunidades
  }

  async getOportunidade(id: number): Promise<Oportunidade> {
    const response = await axios.get(`${API_URL}/companies/oportunidades/${id}`)
    return response.data.oportunidade
  }

  async createOportunidade(data: CreateOportunidadeData): Promise<Oportunidade> {
    const response = await axios.post(`${API_URL}/companies/oportunidades`, data)
    return response.data.oportunidade
  }

  async updateOportunidade(id: number, data: UpdateOportunidadeData): Promise<Oportunidade> {
    const response = await axios.put(`${API_URL}/companies/oportunidades/${id}`, data)
    return response.data.oportunidade
  }

  async deleteOportunidade(id: number): Promise<void> {
    await axios.delete(`${API_URL}/companies/oportunidades/${id}`)
  }

  async getCandidates(oportunidadeId: number): Promise<{
    oportunidade: { id: number; titulo: string }
    candidaturas: Candidatura[]
  }> {
    const response = await axios.get(`${API_URL}/companies/oportunidades/${oportunidadeId}/candidates`)
    return response.data
  }

  async uploadLogo(formData: FormData, onUploadProgress?: (progressEvent: any) => void): Promise<any> {
    const response = await axios.post(`${API_URL}/companies/logo`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress
    })
    return response.data
  }

  async removeLogo(): Promise<void> {
    await axios.delete(`${API_URL}/companies/logo`)
  }
}

export const companyBackofficeService = new CompanyBackofficeService()
