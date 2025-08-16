import axios from 'axios'
import { apiUrl } from '@/utils/urlBuilder'

const API_URL = apiUrl()

export interface CompanyRegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  website?: string
  sector?: string
}

export interface CompanyLoginData {
  email: string
  password: string
}

export interface Company {
  id: number
  name: string
  email: string
  website?: string
  sector?: string
  email_verified_at?: string
}

export interface CompanyAuthResponse {
  message: string
  token?: string
  company?: Company
}

class CompanyAuthService {
  private token: string | null = null
  public api = axios.create({
    baseURL: API_URL
  })

  constructor() {
    this.token = localStorage.getItem('company_token')
    if (this.token) {
      this.setAuthHeader()
    }
  }

  private setAuthHeader() {
    if (this.token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      this.api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
    } else {
      delete axios.defaults.headers.common['Authorization']
      delete this.api.defaults.headers.common['Authorization']
    }
  }

  async register(data: CompanyRegisterData): Promise<CompanyAuthResponse> {
    const response = await axios.post(`${API_URL}/companies/register`, data)
    return response.data
  }

  async login(data: CompanyLoginData): Promise<CompanyAuthResponse> {
    const response = await axios.post(`${API_URL}/companies/login`, data)
    
    if (response.data.token) {
      this.token = response.data.token
      localStorage.setItem('company_token', this.token!)
      this.setAuthHeader()
    }
    
    return response.data
  }

  async logout(): Promise<void> {
    if (this.token) {
      try {
        await axios.post(`${API_URL}/companies/logout`)
      } catch (error) {
        console.error('Logout error:', error)
      }
    }
    
    this.token = null
    localStorage.removeItem('company_token')
    this.setAuthHeader()
  }

  async me(): Promise<Company> {
    const response = await axios.get(`${API_URL}/companies/me`)
    return response.data.company
  }

  async resendVerification(email: string): Promise<{ message: string }> {
    const response = await axios.post(`${API_URL}/companies/email/resend`, { email })
    return response.data
  }

  isAuthenticated(): boolean {
    return !!this.token
  }

  getToken(): string | null {
    return this.token
  }
}

export const companyAuthService = new CompanyAuthService()
