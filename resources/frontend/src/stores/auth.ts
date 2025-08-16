import { ref, computed } from 'vue'

interface User {
  id: number
  name: string
  email: string
  type: 'company' | 'candidato'
}

// Global reactive state
const user = ref<User | null>(null)
const token = ref<string | null>(null)

export const useAuthStore = () => {
  const isAuthenticated = computed(() => !!token.value)
  const userType = computed(() => user.value?.type || null)

  const setAuth = (userData: User, authToken: string) => {
    user.value = userData
    token.value = authToken
    localStorage.setItem('token', authToken)
    localStorage.setItem('user', JSON.stringify(userData))
  }

  const logout = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    localStorage.removeItem('company_token')
    localStorage.removeItem('candidato_token')
  }

  const init = () => {
    // Tentar recuperar dados do localStorage
    const storedToken = localStorage.getItem('token') || 
                       localStorage.getItem('company_token') || 
                       localStorage.getItem('candidato_token')
    const storedUser = localStorage.getItem('user')

    if (storedToken && storedUser) {
      try {
        token.value = storedToken
        user.value = JSON.parse(storedUser)
      } catch (error) {
        console.error('Erro ao recuperar dados do usuário:', error)
        logout()
      }
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    userType,
    setAuth,
    logout,
    init
  }
}
