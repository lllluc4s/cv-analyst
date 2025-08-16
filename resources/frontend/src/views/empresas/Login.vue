<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Login de Empresa
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Aceda ao backoffice da sua empresa
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form class="space-y-6" @submit.prevent="handleSubmit">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email
            </label>
            <div class="mt-1">
              <input
                id="email"
                v-model="form.email"
                name="email"
                type="email"
                required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="empresa@email.com"
              />
            </div>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Palavra-passe
            </label>
            <div class="mt-1">
              <input
                id="password"
                v-model="form.password"
                name="password"
                type="password"
                required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>
          </div>

          <div v-if="error" class="rounded-md bg-red-50 p-4">
            <div class="flex">
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  Erro no login
                </h3>
                <div class="mt-2 text-sm text-red-700">
                  <p>{{ error }}</p>
                  <p v-if="error.includes('não verificado')" class="mt-2">
                    <button
                      @click="resendVerification"
                      :disabled="resendLoading"
                      class="text-blue-600 hover:text-blue-500 font-medium"
                    >
                      {{ resendLoading ? 'A reenviar...' : 'Reenviar email de verificação' }}
                    </button>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div v-if="resendSuccess" class="rounded-md bg-green-50 p-4">
            <div class="flex">
              <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">
                  Email enviado!
                </h3>
                <div class="mt-2 text-sm text-green-700">
                  <p>{{ resendSuccess }}</p>
                </div>
              </div>
            </div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              {{ loading ? 'A entrar...' : 'Entrar' }}
            </button>
          </div>

          <div class="text-center">
            <p class="text-sm text-gray-600">
              Ainda não tem uma conta?
              <router-link to="/empresas/register" class="font-medium text-blue-600 hover:text-blue-500">
                Registar empresa
              </router-link>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { companyAuthService } from '@/services/companyAuth'

const router = useRouter()

const form = reactive({
  email: '',
  password: ''
})

const loading = ref(false)
const resendLoading = ref(false)
const error = ref('')
const resendSuccess = ref('')

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  resendSuccess.value = ''

  try {
    await companyAuthService.login(form)
    router.push('/')
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro interno do servidor'
  } finally {
    loading.value = false
  }
}

const resendVerification = async () => {
  if (!form.email) {
    error.value = 'Por favor, insira o email para reenviar a verificação'
    return
  }

  resendLoading.value = true
  resendSuccess.value = ''

  try {
    await companyAuthService.resendVerification(form.email)
    resendSuccess.value = 'Email de verificação reenviado. Verifique a sua caixa de entrada.'
    error.value = ''
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro ao reenviar email'
  } finally {
    resendLoading.value = false
  }
}
</script>
