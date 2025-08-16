<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-md mx-auto pt-8 px-4">
      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
          Login - Candidato
        </h2>
        
        <form @submit.prevent="login" class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email
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
            <label for="password" class="block text-sm font-medium text-gray-700">
              Password
            </label>
            <input
              v-model="form.password"
              type="password"
              id="password"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div v-if="error" class="text-red-600 text-sm">
            {{ error }}
          </div>
          
          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {{ loading ? 'Entrando...' : 'Entrar' }}
          </button>
        </form>
        
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            Não tem conta? 
            <router-link to="/candidatos/register" class="font-medium text-blue-600 hover:text-blue-500">
              Criar conta
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
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref('')

const login = async () => {
  error.value = ''
  loading.value = true
  
  try {
    await candidatoAuthService.login(form.value)
    router.push('/')
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro ao fazer login'
  } finally {
    loading.value = false
  }
}
</script>
