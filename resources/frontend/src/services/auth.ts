import { ref, computed } from 'vue'
import api from './api'

interface Company {
  id: number
  name: string
  email: string
  website?: string
  sector?: string
}

const company = ref<Company | null>(null)
const isAuthenticated = computed(() => !!company.value)

export const useAuth = () => {
  const login = async (email: string, password: string) => {
    try {
      const response = await api.post('/companies/login', {
        email,
        password
      })
      
      const { token, company: companyData } = response.data
      
      localStorage.setItem('company_token', token)
      company.value = companyData
      
      return { success: true, data: response.data }
    } catch (error: any) {
      return { 
        success: false, 
        message: error.response?.data?.message || 'Erro ao fazer login' 
      }
    }
  }

  const logout = async () => {
    try {
      await api.post('/companies/logout')
    } catch (error) {
      console.error('Erro ao fazer logout:', error)
    } finally {
      localStorage.removeItem('company_token')
      company.value = null
    }
  }

  const checkAuth = async () => {
    const token = localStorage.getItem('company_token')
    if (!token) {
      company.value = null
      return false
    }

    try {
      const response = await api.get('/companies/me')
      company.value = response.data.company || response.data
      return true
    } catch (error) {
      localStorage.removeItem('company_token')
      company.value = null
      return false
    }
  }

  const register = async (data: {
    name: string
    email: string
    password: string
    password_confirmation: string
    website?: string
    sector?: string
  }) => {
    try {
      const response = await api.post('/companies/register', data)
      return { success: true, data: response.data }
    } catch (error: any) {
      return { 
        success: false, 
        message: error.response?.data?.message || 'Erro ao registrar empresa' 
      }
    }
  }

  return {
    company: computed(() => company.value),
    isAuthenticated,
    login,
    logout,
    checkAuth,
    register
  }
}

export const authService = useAuth()
