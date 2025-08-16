<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Carregando formulário...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-8 text-center">
        <div class="flex justify-center mb-4">
          <svg class="w-16 h-16 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-red-800 mb-2">Ops! Algo deu errado</h2>
        <p class="text-red-600">{{ error }}</p>
      </div>

      <!-- Already Responded -->
      <div v-else-if="feedback && feedback.respondido_em" class="bg-green-50 border border-green-200 rounded-lg p-8 text-center">
        <div class="flex justify-center mb-4">
          <svg class="w-16 h-16 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-green-800 mb-2">Obrigado!</h2>
        <p class="text-green-600 mb-4">Você já respondeu este questionário de feedback.</p>
        <div class="bg-white rounded-lg p-4 inline-block">
          <p class="text-sm text-gray-600">Respondido em: {{ formatDate(feedback.respondido_em) }}</p>
          <div class="flex justify-center mt-2">
            <div class="flex items-center">
              <span class="text-sm text-gray-500 mr-2">Sua avaliação:</span>
              <div class="flex">
                <svg
                  v-for="star in 5"
                  :key="star"
                  :class="star <= feedback.avaliacao_processo ? 'text-yellow-400' : 'text-gray-300'"
                  class="w-5 h-5"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Feedback Form -->
      <div v-else-if="feedback" class="bg-white shadow-xl rounded-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6 text-white">
          <div class="flex items-center justify-center mb-4">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h1 class="text-3xl font-bold text-center mb-2">Parabéns pela contratação!</h1>
          <p class="text-blue-100 text-center">Gostaríamos de conhecer a sua experiência no processo de recrutamento</p>
        </div>

        <!-- Company & Position Info -->
        <div class="bg-gray-50 px-8 py-4 border-b">
          <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-900">{{ oportunidade?.titulo }}</h3>
            <p class="text-gray-600">{{ company?.name }}</p>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitFeedback" class="px-8 py-8">
          <!-- Rating -->
          <div class="mb-8">
            <label class="block text-lg font-medium text-gray-900 mb-4">
              Como avaliaria o processo de recrutamento? *
            </label>
            <div class="flex justify-center space-x-2 mb-2">
              <button
                v-for="star in 5"
                :key="star"
                type="button"
                @click="form.avaliacao_processo = star"
                :class="star <= form.avaliacao_processo ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-300'"
                class="transition-colors duration-200 focus:outline-none"
              >
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </button>
            </div>
            <div class="flex justify-center">
              <div class="text-sm text-gray-500 flex space-x-8">
                <span>1 - Muito mau</span>
                <span>5 - Excelente</span>
              </div>
            </div>
            <div v-if="errors.avaliacao_processo" class="mt-2 text-red-600 text-sm text-center">
              {{ errors.avaliacao_processo[0] }}
            </div>
          </div>

          <!-- What did you like -->
          <div class="mb-6">
            <label for="gostou_mais" class="block text-lg font-medium text-gray-900 mb-2">
              O que mais gostou no processo?
            </label>
            <textarea
              id="gostou_mais"
              v-model="form.gostou_mais"
              rows="4"
              placeholder="Partilhe connosco os aspetos que mais apreciou durante o processo de recrutamento..."
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              :class="{ 'border-red-500': errors.gostou_mais }"
            ></textarea>
            <div v-if="errors.gostou_mais" class="mt-1 text-red-600 text-sm">
              {{ errors.gostou_mais[0] }}
            </div>
          </div>

          <!-- What could be improved -->
          <div class="mb-8">
            <label for="poderia_melhorar" class="block text-lg font-medium text-gray-900 mb-2">
              O que poderia ter sido melhorado?
            </label>
            <textarea
              id="poderia_melhorar"
              v-model="form.poderia_melhorar"
              rows="4"
              placeholder="Partilhe as suas sugestões para melhorar a experiência de futuros candidatos..."
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              :class="{ 'border-red-500': errors.poderia_melhorar }"
            ></textarea>
            <div v-if="errors.poderia_melhorar" class="mt-1 text-red-600 text-sm">
              {{ errors.poderia_melhorar[0] }}
            </div>
          </div>

          <!-- Submit Button -->
          <div class="text-center">
            <button
              type="submit"
              :disabled="submitting || !form.avaliacao_processo"
              class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105"
            >
              <svg v-if="submitting" class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ submitting ? 'Enviando...' : 'Enviar Feedback' }}
            </button>
          </div>

          <!-- Privacy Note -->
          <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
              🔒 O seu feedback é anónimo e será usado apenas para melhorar o nosso processo de recrutamento.
            </p>
          </div>
        </form>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="mt-6 bg-green-50 border border-green-200 rounded-lg p-6 text-center">
        <div class="flex justify-center mb-4">
          <svg class="w-12 h-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-green-800 mb-2">Feedback enviado com sucesso!</h3>
        <p class="text-green-600">{{ successMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { feedbackRecrutamentoService } from '@/services/feedbackRecrutamento'

const route = useRoute()
const token = route.params.token as string

// State
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const successMessage = ref('')

// Data
const feedback = ref<any>(null)
const oportunidade = ref<any>(null)
const company = ref<any>(null)

// Form
const form = ref({
  avaliacao_processo: 0,
  gostou_mais: '',
  poderia_melhorar: ''
})

const errors = ref<any>({})

// Methods
const loadFeedback = async () => {
  try {
    loading.value = true
    const response = await feedbackRecrutamentoService.getFeedback(token)
    
    feedback.value = response.feedback
    oportunidade.value = response.oportunidade
    company.value = response.company
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro ao carregar formulário'
  } finally {
    loading.value = false
  }
}

const submitFeedback = async () => {
  try {
    submitting.value = true
    errors.value = {}
    
    const response = await feedbackRecrutamentoService.submitFeedback(token, form.value)
    
    successMessage.value = response.message
    feedback.value = response.feedback
  } catch (err: any) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      error.value = err.response?.data?.message || 'Erro ao enviar feedback'
    }
  } finally {
    submitting.value = false
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString('pt-PT')
}

onMounted(() => {
  loadFeedback()
})
</script>
