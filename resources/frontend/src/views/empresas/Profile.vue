<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
          <h1 class="text-2xl font-bold text-gray-900">Perfil da Empresa</h1>
          <p class="text-gray-600">Gerencie as informações da sua empresa</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white shadow rounded-lg p-6">
        <div class="animate-pulse">
          <div class="h-4 bg-gray-200 rounded w-1/4 mb-4"></div>
          <div class="h-10 bg-gray-200 rounded mb-4"></div>
          <div class="h-4 bg-gray-200 rounded w-1/4 mb-4"></div>
          <div class="h-10 bg-gray-200 rounded mb-4"></div>
        </div>
      </div>

      <!-- Profile Form -->
      <div v-else class="bg-white shadow rounded-lg">
        <form @submit.prevent="updateProfile" class="p-6 space-y-6">
          <!-- Logo Section -->
          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Logo da Empresa</h3>
            
            <div class="flex items-center space-x-6">
              <!-- Current Logo -->
              <div class="flex-shrink-0">
                <div class="w-20 h-20 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden">
                  <img 
                    v-if="company.logo_url" 
                    :src="company.logo_url" 
                    :alt="`Logo da ${company.name}`"
                    class="w-full h-full object-contain"
                  >
                  <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>

              <!-- Logo Upload -->
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Alterar Logo
                </label>
                <input 
                  type="file" 
                  ref="logoInput"
                  @change="handleLogoUpload"
                  accept="image/*"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                >
                <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF até 2MB</p>
                
                <!-- Remove Logo -->
                <button 
                  v-if="company.logo_url"
                  @click="removeLogo"
                  type="button"
                  class="mt-2 text-sm text-red-600 hover:text-red-800"
                >
                  Remover logo
                </button>
              </div>
            </div>
          </div>

          <!-- Company Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Nome da Empresa *
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.name }"
              >
              <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</p>
            </div>

            <!-- Email (readonly) -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                E-mail
              </label>
              <input
                id="email"
                :value="company.email"
                type="email"
                readonly
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500 cursor-not-allowed"
              >
              <p class="text-xs text-gray-500 mt-1">O e-mail não pode ser alterado</p>
            </div>

            <!-- Website -->
            <div>
              <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                Website
              </label>
              <input
                id="website"
                v-model="form.website"
                type="url"
                placeholder="https://exemplo.com"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.website }"
              >
              <p v-if="errors.website" class="text-red-500 text-sm mt-1">{{ errors.website[0] }}</p>
            </div>

            <!-- Sector -->
            <div>
              <label for="sector" class="block text-sm font-medium text-gray-700 mb-2">
                Setor
              </label>
              <input
                id="sector"
                v-model="form.sector"
                type="text"
                placeholder="Ex: Tecnologia, Saúde, Educação"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.sector }"
              >
              <p v-if="errors.sector" class="text-red-500 text-sm mt-1">{{ errors.sector[0] }}</p>
            </div>
          </div>

          <!-- Company Slug (readonly) -->
          <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
              URL Pública
            </label>
            <div class="flex">
              <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                /empresas/
              </span>
              <input
                id="slug"
                :value="company.slug"
                type="text"
                readonly
                class="flex-1 px-3 py-2 border border-gray-300 rounded-r-md shadow-sm bg-gray-50 text-gray-500 cursor-not-allowed"
              >
            </div>
            <p class="text-xs text-gray-500 mt-1">Esta é a URL pública da sua empresa</p>
          </div>

          <!-- Submit Button -->
          <div class="pt-6 border-t border-gray-200">
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="resetForm"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="submitting">Salvando...</span>
                <span v-else>Salvar Alterações</span>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Success Message -->
      <div 
        v-if="successMessage" 
        class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50"
      >
        {{ successMessage }}
      </div>

      <!-- Error Message -->
      <div 
        v-if="errorMessage" 
        class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50"
      >
        {{ errorMessage }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { UrlBuilder } from '@/utils/urlBuilder'

const router = useRouter()

// API URL configuration
const API_URL = UrlBuilder.api()

// Reactive data
const loading = ref(true)
const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const errors = ref<any>({})

const company = ref({
  id: null,
  name: '',
  email: '',
  website: '',
  sector: '',
  logo_url: null,
  logo_path: null,
  slug: '',
  created_at: null,
  email_verified_at: null
})

const form = reactive({
  name: '',
  website: '',
  sector: ''
})

// Refs
const logoInput = ref<HTMLInputElement | null>(null)

// Methods
const fetchProfile = async () => {
  try {
    loading.value = true
    
    const token = localStorage.getItem('company_token')
    if (!token) {
      router.push('/empresas/login')
      return
    }

    const response = await fetch(`${API_URL}/companies/profile`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })

    if (!response.ok) {
      if (response.status === 401) {
        localStorage.removeItem('company_token')
        router.push('/empresas/login')
        return
      }
      throw new Error('Erro ao carregar perfil')
    }

    const data = await response.json()
    company.value = data.company
    
    // Populate form with current data
    form.name = data.company.name || ''
    form.website = data.company.website || ''
    form.sector = data.company.sector || ''

  } catch (error: any) {
    console.error('Erro ao carregar perfil:', error)
    showError('Erro ao carregar perfil da empresa')
  } finally {
    loading.value = false
  }
}

const updateProfile = async () => {
  try {
    submitting.value = true
    errors.value = {}
    
    const token = localStorage.getItem('company_token')
    if (!token) {
      router.push('/empresas/login')
      return
    }

    const response = await fetch(`${API_URL}/companies/profile`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(form)
    })

    const data = await response.json()

    if (!response.ok) {
      if (response.status === 422) {
        errors.value = data.errors || {}
        return
      }
      if (response.status === 401) {
        localStorage.removeItem('company_token')
        router.push('/empresas/login')
        return
      }
      throw new Error(data.message || 'Erro ao atualizar perfil')
    }

    company.value = data.company
    showSuccess('Perfil atualizado com sucesso!')

  } catch (error: any) {
    console.error('Erro ao atualizar perfil:', error)
    showError(error.message || 'Erro ao atualizar perfil')
  } finally {
    submitting.value = false
  }
}

const handleLogoUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  try {
    const token = localStorage.getItem('company_token')
    if (!token) {
      router.push('/empresas/login')
      return
    }

    const formData = new FormData()
    formData.append('logo', file)

    const response = await fetch(`${API_URL}/companies/logo`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`
      },
      body: formData
    })

    const data = await response.json()

    if (!response.ok) {
      if (response.status === 401) {
        localStorage.removeItem('company_token')
        router.push('/empresas/login')
        return
      }
      throw new Error(data.message || 'Erro ao fazer upload do logo')
    }

    company.value.logo_url = data.logo_url
    company.value.logo_path = data.logo_path
    showSuccess('Logo atualizado com sucesso!')

    // Clear file input
    if (logoInput.value) {
      logoInput.value.value = ''
    }

  } catch (error: any) {
    console.error('Erro ao fazer upload do logo:', error)
    showError(error.message || 'Erro ao fazer upload do logo')
  }
}

const removeLogo = async () => {
  if (!confirm('Tem certeza que deseja remover o logo?')) return

  try {
    const token = localStorage.getItem('company_token')
    if (!token) {
      router.push('/empresas/login')
      return
    }

    const response = await fetch(`${API_URL}/companies/logo`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })

    if (!response.ok) {
      if (response.status === 401) {
        localStorage.removeItem('company_token')
        router.push('/empresas/login')
        return
      }
      const data = await response.json()
      throw new Error(data.message || 'Erro ao remover logo')
    }

    company.value.logo_url = null
    company.value.logo_path = null
    showSuccess('Logo removido com sucesso!')

  } catch (error: any) {
    console.error('Erro ao remover logo:', error)
    showError(error.message || 'Erro ao remover logo')
  }
}

const resetForm = () => {
  form.name = company.value.name || ''
  form.website = company.value.website || ''
  form.sector = company.value.sector || ''
  errors.value = {}
}

const showSuccess = (message: string) => {
  successMessage.value = message
  setTimeout(() => {
    successMessage.value = ''
  }, 3000)
}

const showError = (message: string) => {
  errorMessage.value = message
  setTimeout(() => {
    errorMessage.value = ''
  }, 5000)
}

// Lifecycle
onMounted(() => {
  fetchProfile()
})
</script>
