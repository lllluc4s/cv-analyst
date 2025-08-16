import api from './api'

interface CandidatoRegisterData {
  nome: string
  apelido: string
  email: string
  telefone: string
  cv_path: string
  linkedin_url?: string
  password: string
  password_confirmation: string
}

interface CandidatoLoginData {
  email: string
  password: string
}

interface Candidato {
  id: number
  nome: string
  apelido: string
  email: string
  telefone?: string
  foto_url?: string
  skills?: string[]
  experiencia_profissional?: any[]
  formacao_academica?: any[]
  cv_path?: string
  linkedin_url?: string
  is_searchable?: boolean | number
  consentimento_emails?: boolean | number
}

class CandidatoAuthService {
  private static readonly TOKEN_KEY = 'candidato_token'
  private static readonly USER_KEY = 'candidato_user'

  async register(data: CandidatoRegisterData) {
    const response = await api.post('/candidatos/register', data)
    return response.data
  }

  async registerWithFile(formData: FormData) {
    const response = await api.post('/candidatos/register', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  }

  async login(data: CandidatoLoginData) {
    const response = await api.post('/candidatos/login', data)
    const { token, candidato } = response.data
    
    localStorage.setItem(CandidatoAuthService.TOKEN_KEY, token)
    localStorage.setItem(CandidatoAuthService.USER_KEY, JSON.stringify(candidato))
    
    return response.data
  }

  async logout() {
    try {
      await api.post('/candidatos/logout')
    } catch (error) {
      // Continue with logout even if API call fails
    } finally {
      this.clearAuth()
    }
  }

  async me() {
    const response = await api.get('/candidatos/me')
    const candidato = response.data.candidato
    localStorage.setItem(CandidatoAuthService.USER_KEY, JSON.stringify(candidato))
    return candidato
  }

  getCurrentCandidato(): Candidato | null {
    const userData = localStorage.getItem(CandidatoAuthService.USER_KEY)
    return userData ? JSON.parse(userData) : null
  }

  getToken(): string | null {
    return localStorage.getItem(CandidatoAuthService.TOKEN_KEY)
  }

  isAuthenticated(): boolean {
    return !!this.getToken()
  }

  clearAuth() {
    localStorage.removeItem(CandidatoAuthService.TOKEN_KEY)
    localStorage.removeItem(CandidatoAuthService.USER_KEY)
  }

  initializeAuth() {
    // O interceptor do API service já cuida do token automaticamente
    // Nada precisa ser feito aqui
  }

  async updateProfile(data: FormData) {
    console.log('Enviando dados do perfil:')
    // Log dos dados do FormData
    for (let [key, value] of data.entries()) {
      console.log(`${key}:`, value)
    }
    
    // Laravel tem problemas com FormData em requisições PUT
    // Usar POST com method spoofing
    data.append('_method', 'PUT')
    
    const response = await api.post('/candidatos/profile', data)
    
    console.log('Resposta da API:', response.data)
    
    // Atualizar dados locais se o candidato foi retornado
    if (response.data.candidato) {
      const candidato = response.data.candidato
      localStorage.setItem(CandidatoAuthService.USER_KEY, JSON.stringify(candidato))
    }
    
    return response.data
  }

  async getCandidaturas(page = 1) {
    const response = await api.get(`/candidatos/candidaturas?page=${page}`)
    return response.data
  }

  async candidatar(oportunidadeId: number) {
    const response = await api.post(`/candidatos/oportunidades/${oportunidadeId}/candidatar`)
    return response.data
  }

  async canApply(oportunidadeId: number) {
    const response = await api.get(`/candidatos/oportunidades/${oportunidadeId}/can-apply`)
    return response.data
  }

  // Métodos de privacidade
  async updatePrivacySettings(data: { is_searchable: boolean, consentimento_emails?: boolean }) {
    const response = await api.put('/candidatos/privacy/searchability', data)
    return response.data
  }

  async exportData() {
    const response = await api.get('/candidatos/privacy/export-data', {
      responseType: 'json'
    })
    
    // Criar um blob com os dados JSON formatados para download
    const dataStr = JSON.stringify(response.data, null, 2)
    const dataBlob = new Blob([dataStr], { type: 'application/json' })
    
    return { data: dataBlob }
  }

  async deleteAccount(data: { password: string; confirmation: string }) {
    const response = await api.post('/candidatos/privacy/delete-account', data)
    this.clearAuth()
    return response.data
  }

  // Profile photo methods
  async uploadProfilePhoto(formData: FormData) {
    const response = await api.post('/candidatos/profile-photo', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  }

  async removeProfilePhoto() {
    const response = await api.delete('/candidatos/profile-photo')
    return response.data
  }
}

export const candidatoAuthService = new CandidatoAuthService()

// Initialize auth on service creation
candidatoAuthService.initializeAuth()
