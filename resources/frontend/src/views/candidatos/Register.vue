<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-md mx-auto pt-8 px-4">
      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
          Criar Conta - Candidato
        </h2>
        
        <form @submit.prevent="register" class="space-y-4">
          <div>
            <label for="nome" class="block text-sm font-medium text-gray-700">
              Nome *
            </label>
            <input
              v-model="form.nome"
              type="text"
              id="nome"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div>
            <label for="apelido" class="block text-sm font-medium text-gray-700">
              Apelido *
            </label>
            <input
              v-model="form.apelido"
              type="text"
              id="apelido"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email *
            </label>
            <input
              v-model="form.email"
              type="email"
              id="email"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div>
            <label for="telefone" class="block text-sm font-medium text-gray-700">
              Telefone *
            </label>
            <input
              v-model="form.telefone"
              type="tel"
              id="telefone"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label for="data_nascimento" class="block text-sm font-medium text-gray-700">
              Data de Nascimento *
            </label>
            <input
              v-model="form.data_nascimento"
              type="date"
              id="data_nascimento"
              required
              :max="new Date().toISOString().split('T')[0]"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div>
            <label for="cv_file" class="block text-sm font-medium text-gray-700">
              Currículo (CV) *
            </label>
            <input
              @change="handleFileUpload"
              type="file"
              id="cv_file"
              accept=".pdf,.doc,.docx"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            />
            <p class="mt-1 text-sm text-gray-500">
              Formatos aceitos: PDF, DOC, DOCX (máx. 5MB)
            </p>
          </div>
          
          <div>
            <label for="linkedin_url" class="block text-sm font-medium text-gray-700">
              LinkedIn (Opcional)
            </label>
            <input
              v-model="form.linkedin_url"
              type="url"
              id="linkedin_url"
              placeholder="https://linkedin.com/in/seu-perfil"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Password *
            </label>
            <input
              v-model="form.password"
              type="password"
              id="password"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirmar Password *
            </label>
            <input
              v-model="form.password_confirmation"
              type="password"
              id="password_confirmation"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div v-if="error" class="text-red-600 text-sm">
            {{ error }}
          </div>
          
          <div v-if="success" class="text-green-600 text-sm">
            {{ success }}
          </div>
          
          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {{ loading ? 'Criando conta...' : 'Criar Conta' }}
          </button>
        </form>
        
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            Já tem conta? 
            <router-link to="/candidatos/login" class="font-medium text-blue-600 hover:text-blue-500">
              Faça login
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { candidatoAuthService } from '../../services/candidatoAuth'

const router = useRouter()

const form = ref({
  nome: '',
  apelido: '',
  email: '',
  telefone: '',
  data_nascimento: '',
  linkedin_url: '',
  password: '',
  password_confirmation: ''
})

const cvFile = ref<File | null>(null)
const loading = ref(false)
const error = ref('')
const success = ref('')

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    
    // Validar tamanho do arquivo (5MB)
    if (file.size > 5 * 1024 * 1024) {
      error.value = 'O arquivo é muito grande. Máximo permitido: 5MB'
      return
    }
    
    // Validar tipo do arquivo
    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
    if (!allowedTypes.includes(file.type)) {
      error.value = 'Tipo de arquivo não permitido. Use PDF, DOC ou DOCX'
      return
    }
    
    cvFile.value = file
    error.value = ''
  }
}

const register = async () => {
  error.value = ''
  success.value = ''
  loading.value = true
  
  if (!cvFile.value) {
    error.value = 'Por favor, selecione um arquivo de CV'
    loading.value = false
    return
  }
  
  try {
    // Criar FormData para incluir o arquivo
    const formData = new FormData()
    formData.append('nome', form.value.nome)
    formData.append('apelido', form.value.apelido)
    formData.append('email', form.value.email)
    formData.append('telefone', form.value.telefone)
    formData.append('data_nascimento', form.value.data_nascimento)
    formData.append('linkedin_url', form.value.linkedin_url)
    formData.append('password', form.value.password)
    formData.append('password_confirmation', form.value.password_confirmation)
    formData.append('cv_file', cvFile.value)
    
    const response = await candidatoAuthService.registerWithFile(formData)
    success.value = response.message
    
    // Redirect to login after 2 seconds
    setTimeout(() => {
      router.push('/candidatos/login')
    }, 2000)
    
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro ao criar conta'
  } finally {
    loading.value = false
  }
}
</script>
