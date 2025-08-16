<template>
  <div class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
          <div class="flex items-center justify-between">
            <div>
              <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                  <li>
                    <div>
                      <router-link 
                        to="/empresas/dashboard" 
                        class="text-gray-400 hover:text-gray-500"
                      >
                        <svg class="flex-shrink-0 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        <span class="sr-only">Home</span>
                      </router-link>
                    </div>
                  </li>
                  <li>
                    <div class="flex items-center">
                      <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                      <span class="ml-4 text-sm font-medium text-gray-500">{{ oportunidade?.titulo }}</span>
                    </div>
                  </li>
                  <li>
                    <div class="flex items-center">
                      <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                      <span class="ml-4 text-sm font-medium text-gray-500">Feedbacks</span>
                    </div>
                  </li>
                </ol>
              </nav>
              <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                Feedbacks do Processo de Recrutamento
              </h1>
              <p class="mt-1 text-sm text-gray-500">
                Opiniões dos colaboradores sobre a experiência de recrutamento
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Opportunity Tabs -->
      <OpportunityTabs :oportunidade-id="id" />

      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin mx-auto h-8 w-8 text-blue-600">
          <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <p class="mt-2 text-gray-500">A carregar feedbacks...</p>
      </div>

      <div v-else>
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-6">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total de Feedbacks</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats?.total_feedbacks || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Avaliação Média</dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ stats?.avaliacao_media ? stats.avaliacao_media.toFixed(1) : '0.0' }}/5
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.01M15 10h1.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Feedbacks Positivos</dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ ((stats?.distribuicao_avaliacoes['4'] || 0) + (stats?.distribuicao_avaliacoes['5'] || 0)) }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Colaboradores Contratados</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ colaboradores?.length || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Gráfico de Distribuição de Avaliações -->
        <div v-if="stats && stats.total_feedbacks > 0" class="bg-white shadow rounded-lg p-6 mb-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">Distribuição de Avaliações</h3>
          <div class="space-y-4">
            <div v-for="rating in [5, 4, 3, 2, 1]" :key="rating" class="flex items-center">
              <div class="flex items-center w-20">
                <span class="text-sm font-medium text-gray-700 mr-2">{{ rating }}</span>
                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
              </div>
              <div class="flex-1 mx-4">
                <div class="bg-gray-200 rounded-full h-4">
                  <div 
                    class="h-4 rounded-full transition-all duration-300"
                    :class="{
                      'bg-green-500': rating >= 4,
                      'bg-yellow-500': rating === 3,
                      'bg-red-500': rating <= 2
                    }"
                    :style="{ 
                      width: getPercentage(rating) 
                    }"
                  ></div>
                </div>
              </div>
              <span class="text-sm font-medium text-gray-700 w-12 text-right">
                {{ (stats.distribuicao_avaliacoes as any)[rating] }}
              </span>
            </div>
          </div>
        </div>

        <!-- Lista de Feedbacks -->
        <div v-if="feedbacks.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Feedbacks Detalhados</h3>
            <p class="text-sm text-gray-600 mt-1">Opiniões individuais dos colaboradores</p>
          </div>
          
          <div class="divide-y divide-gray-200">
            <div v-for="feedback in feedbacks" :key="feedback.id" class="px-6 py-4">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-gray-900">
                      {{ feedback.colaborador.nome_completo }}
                    </h4>
                    <div class="flex items-center">
                      <div class="flex mr-2">
                        <svg
                          v-for="star in 5"
                          :key="star"
                          :class="star <= feedback.avaliacao_processo ? 'text-yellow-400' : 'text-gray-300'"
                          class="w-4 h-4"
                          fill="currentColor"
                          viewBox="0 0 20 20"
                        >
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                      </div>
                      <span class="text-xs text-gray-500">
                        {{ formatDate(feedback.respondido_em) }}
                      </span>
                    </div>
                  </div>
                  
                  <div v-if="feedback.gostou_mais" class="mb-4">
                    <h5 class="text-sm font-medium text-green-800 mb-1">✅ O que mais gostou:</h5>
                    <p class="text-sm text-gray-700 bg-green-50 p-3 rounded-md">
                      {{ feedback.gostou_mais }}
                    </p>
                  </div>
                  
                  <div v-if="feedback.poderia_melhorar" class="mb-2">
                    <h5 class="text-sm font-medium text-orange-800 mb-1">💡 Sugestões de melhoria:</h5>
                    <p class="text-sm text-gray-700 bg-orange-50 p-3 rounded-md">
                      {{ feedback.poderia_melhorar }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12 bg-white shadow rounded-lg">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Ainda não há feedbacks</h3>
          <p class="mt-1 text-sm text-gray-500">
            Os feedbacks aparecerão aqui quando candidatos contratados responderem ao questionário.
          </p>
          <div class="mt-6">
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 max-w-lg mx-auto">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-blue-800">Como funciona?</h3>
                  <div class="mt-2 text-sm text-blue-700">
                    <p>Quando contratar um candidato, é enviado automaticamente um email com um link para feedback sobre o processo de recrutamento.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import OpportunityTabs from '@/components/OpportunityTabs.vue'
import { feedbackEmpresaService, type FeedbackEmpresa, type FeedbackStats } from '@/services/feedbackEmpresa'

const route = useRoute()
const id = route.params.id as string

// State
const loading = ref(true)
const oportunidade = ref<any>(null)
const feedbacks = ref<FeedbackEmpresa[]>([])
const stats = ref<FeedbackStats | null>(null)
const colaboradores = ref<any[]>([])

// Methods
const loadFeedbacks = async () => {
  try {
    loading.value = true
    const response = await feedbackEmpresaService.getFeedbacksOportunidade(parseInt(id))
    
    oportunidade.value = response.oportunidade
    feedbacks.value = response.feedbacks
    stats.value = response.stats
  } catch (error) {
    console.error('Erro ao carregar feedbacks:', error)
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString('pt-PT')
}

const getPercentage = (rating: number) => {
  if (!stats.value || stats.value.total_feedbacks === 0) return '0%'
  const count = stats.value.distribuicao_avaliacoes[rating.toString() as keyof typeof stats.value.distribuicao_avaliacoes] || 0
  const percentage = (count / stats.value.total_feedbacks) * 100
  return `${percentage}%`
}

onMounted(() => {
  loadFeedbacks()
})
</script>
