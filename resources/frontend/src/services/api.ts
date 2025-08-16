import axios from 'axios'
import { apiUrl } from '@/utils/urlBuilder'

const API_BASE_URL = apiUrl()

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'ngrok-skip-browser-warning': 'true'
  },
  timeout: 10000
})

// Interceptor para adicionar token de autenticação
api.interceptors.request.use(request => {
  console.log('Starting Request:', request.method?.toUpperCase(), request.url)
  console.log('Request data type:', request.data instanceof FormData ? 'FormData' : typeof request.data)
  
  // Adicionar token se existir (prioridade: company_token > candidato_token > token)
  const companyToken = localStorage.getItem('company_token')
  const candidatoToken = localStorage.getItem('candidato_token')
  const token = localStorage.getItem('token')
  
  if (companyToken) {
    request.headers.Authorization = `Bearer ${companyToken}`
  } else if (candidatoToken) {
    request.headers.Authorization = `Bearer ${candidatoToken}`
  } else if (token) {
    request.headers.Authorization = `Bearer ${token}`
  }
  
  // Se for FormData, remover Content-Type para deixar o browser definir automaticamente
  if (request.data instanceof FormData) {
    delete request.headers['Content-Type']
    console.log('FormData detected, removing Content-Type header')
  }
  
  return request
})

api.interceptors.response.use(
  response => {
    console.log('Response:', response.status, response.config.url)
    return response
  },
  error => {
    console.error('API Error:', error.response?.status, error.response?.data || error.message)
    
    // Se receber 401, limpar tokens e redirecionar para login
    if (error.response?.status === 401) {
      localStorage.removeItem('company_token')
      localStorage.removeItem('candidato_token')
      localStorage.removeItem('token')
      // Redirecionar para página de login se não estiver nela
      if (!window.location.pathname.includes('login')) {
        window.location.href = '/login'
      }
    }
    
    return Promise.reject(error)
  }
)

export interface SkillComPeso {
  nome: string
  peso?: number
}

export interface Oportunidade {
  id?: number
  titulo: string
  descricao: string
  skills_desejadas: SkillComPeso[]
  localizacao?: string
  slug?: string
  ativa?: boolean
  publica?: boolean
  company_id?: number
  created_at?: string
  updated_at?: string
}

export interface Candidatura {
  id?: number
  oportunidade_id: number
  nome: string
  apelido: string
  slug?: string
  email: string
  telefone: string
  cv_path: string
  linkedin?: string
  rgpd_aceito: boolean
  skills_extraidas?: string[]
  pontuacao_skills?: number
  created_at?: string
  updated_at?: string
  oportunidade?: Oportunidade
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

export interface ReportData {
  oportunidade: Oportunidade
  total_visitas: number
  visitas_unicas: number
  visitas_por_dia: Array<{ data: string; total: number }>
  browsers_mais_usados: Array<{ browser: string; total: number }>
  visitas_por_cidade: Array<{ 
    city: string
    country: string
    region?: string
    latitude?: number
    longitude?: number
    total: number 
  }>
}

export const oportunidadesService = {
  async getAll(page = 1): Promise<PaginatedResponse<Oportunidade>> {
    const response = await api.get(`/oportunidades?page=${page}`)
    return response.data
  },

  async getBySlug(slug: string): Promise<Oportunidade> {
    const response = await api.get(`/oportunidades/slug/${slug}`)
    return response.data
  },

  async create(oportunidade: Omit<Oportunidade, 'id' | 'created_at' | 'updated_at'>): Promise<{ message: string; data: Oportunidade }> {
    const response = await api.post('/oportunidades', oportunidade)
    return response.data
  },

  async update(slug: string, oportunidade: Partial<Oportunidade>): Promise<{ message: string; data: Oportunidade }> {
    const response = await api.put(`/oportunidades/${slug}`, oportunidade)
    return response.data
  },

  async delete(slug: string): Promise<{ message: string }> {
    const response = await api.delete(`/oportunidades/${slug}`)
    return response.data
  },

  async getPublic(page = 1): Promise<PaginatedResponse<Oportunidade>> {
    const response = await api.get(`/oportunidades/publicas?page=${page}`)
    return response.data
  },

  async getReports(slug: string): Promise<ReportData> {
    // Adicionar timestamp para evitar cache
    const timestamp = new Date().getTime()
    const response = await api.get(`/oportunidades/${slug}/reports?_t=${timestamp}`)
    return response.data
  },

  async getCandidaturas(oportunidadeId: number): Promise<{ oportunidade: Oportunidade; candidaturas: Candidatura[] }> {
    const response = await api.get(`/oportunidades/${oportunidadeId}/candidaturas`)
    return response.data
  }
}

export const candidaturasService = {
  async getAll(page = 1): Promise<PaginatedResponse<Candidatura>> {
    const response = await api.get(`/candidaturas?page=${page}`)
    return response.data
  },

  async getById(id: number): Promise<Candidatura> {
    const response = await api.get(`/candidaturas/${id}`)
    return response.data
  },

  async getCandidaturasPorOportunidade(oportunidadeId: number): Promise<{ oportunidade: Oportunidade; candidaturas: Candidatura[] }> {
    const response = await api.get(`/oportunidades/${oportunidadeId}/candidaturas`)
    return response.data
  },

  async atualizarSkills(candidaturaId: number, skills: string[]): Promise<{ message: string; candidatura: Candidatura }> {
    const response = await api.put(`/candidaturas/${candidaturaId}/skills`, {
      skills_extraidas: skills
    })
    return response.data
  },

  async submeter(candidatura: FormData): Promise<{ message: string; data: Candidatura }> {
    // Para upload de arquivos, precisamos mudar o Content-Type
    const response = await api.post('/candidaturas', candidatura, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  },

  async delete(slug: string): Promise<{ message: string }> {
    const response = await api.delete(`/candidaturas/${slug}`)
    return response.data
  },
}

export const reportsApi = {
  async getTendencias(filtros?: { data_inicio?: string; data_fim?: string }): Promise<{
    candidaturas_por_mes: Array<{ mes: string; total_candidaturas: number }>;
    top_skills: Record<string, number>;
    skills_por_mes: Record<string, Record<string, number>>;
    total_candidaturas: number;
    periodo: { inicio: string; fim: string };
  }> {
    const params = new URLSearchParams()
    if (filtros?.data_inicio) {
      params.append('data_inicio', filtros.data_inicio)
    }
    if (filtros?.data_fim) {
      params.append('data_fim', filtros.data_fim)
    }
    
    const url = '/reports/tendencias' + (params.toString() ? '?' + params.toString() : '')
    const response = await api.get(url)
    return response.data
  }
}

export default api
