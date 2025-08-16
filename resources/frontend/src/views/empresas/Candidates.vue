<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
          <div class="flex items-center justify-between">
            <div>
              <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                  <li>
                    <router-link 
                      to="/empresas/dashboard" 
                      class="text-gray-400 hover:text-gray-500"
                    >
                      Dashboard
                    </router-link>
                  </li>
                  <li>
                    <div class="flex items-center">
                      <svg class="flex-shrink-0 h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                      <span class="ml-4 text-sm font-medium text-gray-500">Candidatos</span>
                    </div>
                  </li>
                </ol>
              </nav>
              <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                {{ oportunidade?.titulo }}
              </h1>
              <p class="mt-1 text-sm text-gray-500">
                Gerir candidaturas para esta oportunidade
              </p>
            </div>
            <div class="flex space-x-3">
              <router-link 
                :to="`/empresas/oportunidades/${id}/kanban`"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
              >
                📋 Board Kanban
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Opportunity Tabs -->
      <OpportunityTabs :oportunidade-id="id" />

      <!-- Filters and Actions -->
      <div class="mb-4 flex justify-between items-center">
        <!-- Rating Filter -->
        <div class="flex items-center space-x-4">
          <label class="text-sm font-medium text-gray-700">Filtrar por avaliação:</label>
          <select
            v-model="ratingFilter"
            @change="applyFilters"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Todas as avaliações</option>
            <option value="5">⭐⭐⭐⭐⭐ (5 estrelas)</option>
            <option value="4">⭐⭐⭐⭐ (4+ estrelas)</option>
            <option value="3">⭐⭐⭐ (3+ estrelas)</option>
            <option value="2">⭐⭐ (2+ estrelas)</option>
            <option value="1">⭐ (1+ estrelas)</option>
            <option value="unrated">Sem avaliação</option>
          </select>
        </div>
        
        <!-- Refresh Button -->
        <button
          @click="fetchCandidates"
          :disabled="loading"
          class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
        >
          <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          {{ loading ? 'Atualizando...' : 'Atualizar' }}
        </button>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin mx-auto h-8 w-8 text-blue-600">
          <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <p class="mt-2 text-gray-500">A carregar candidatos...</p>
      </div>

      <div v-else>
        <!-- Stats -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Total de Candidaturas
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ filteredCandidaturas.length }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Candidates List -->
        <div v-if="filteredCandidaturas.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma candidatura</h3>
          <p class="mt-1 text-sm text-gray-500">
            Ainda não recebeu candidaturas para esta oportunidade.
          </p>
        </div>

        <div v-else class="bg-white shadow overflow-hidden sm:rounded-md">
          <ul class="divide-y divide-gray-200">
            <li v-for="candidatura in filteredCandidaturas" :key="candidatura.id">
              <div class="px-4 py-4 sm:px-6">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">
                          {{ candidatura.nome.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="flex items-center">
                        <p class="text-sm font-medium text-gray-900">
                          {{ candidatura.nome }}
                        </p>
                        <span v-if="candidatura.ranking" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          #{{ candidatura.ranking }}
                        </span>
                      </div>
                      <div class="mt-1 flex items-center text-sm text-gray-500">
                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                          <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                        {{ candidatura.email }}
                      </div>
                      <div v-if="candidatura.telefone" class="mt-1 flex items-center text-sm text-gray-500">
                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                        </svg>
                        {{ candidatura.telefone }}
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="text-right">
                      <p v-if="candidatura.score" class="text-sm font-medium text-gray-900">
                        Score: {{ candidatura.score }}
                      </p>
                      <p class="text-xs text-gray-500">
                        {{ formatDate(candidatura.created_at) }}
                      </p>
                      <!-- Avaliação com Estrelas -->
                      <div class="mt-1">
                        <StarRating 
                          :rating="candidatura.company_rating"
                          @update="(rating) => updateCandidateRating(candidatura.id, rating)"
                          size="sm"
                          showText
                        />
                      </div>
                    </div>
                    <div class="flex space-x-2">
                      <a 
                        v-if="candidatura.cv_url" 
                        :href="candidatura.cv_url" 
                        target="_blank" 
                        class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                      >
                        <svg class="mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        CV
                      </a>
                      <a 
                        v-if="candidatura.linkedin_url" 
                        :href="candidatura.linkedin_url" 
                        target="_blank" 
                        class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                      >
                        <svg class="mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                          <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                        </svg>
                        LinkedIn
                      </a>
                    </div>
                  </div>
                </div>
                <div v-if="candidatura.skills && candidatura.skills.length > 0" class="mt-3">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="skill in candidatura.skills" :key="skill" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                      {{ skill }}
                    </span>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import StarRating from '../../components/StarRating.vue'
import OpportunityTabs from '@/components/OpportunityTabs.vue'
import axios from 'axios'

const route = useRoute()
const id = route.params.id as string

const loading = ref(true)
const oportunidade = ref<any>(null)
const candidaturas = ref<any[]>([])
const filteredCandidaturas = ref<any[]>([])
const ratingFilter = ref('')
let refreshInterval: number | null = null

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-PT', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchCandidates = async () => {
  try {
    const token = localStorage.getItem('company_token')
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/companies/oportunidades/${id}/candidates`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'ngrok-skip-browser-warning': 'true'
      }
    })
    
    oportunidade.value = response.data.oportunidade
    candidaturas.value = response.data.candidaturas
    applyFilters() // Aplicar filtros após carregar dados
  } catch (error) {
    console.error('Erro ao buscar candidatos:', error)
  } finally {
    loading.value = false
  }
}

const updateCandidateRating = async (candidaturaId: number, rating: number) => {
  try {
    const token = localStorage.getItem('company_token')
    await axios.patch(`${import.meta.env.VITE_API_URL}/api/candidaturas/${candidaturaId}/rating`, {
      rating: rating
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    
    // Update local data
    const candidatura = candidaturas.value.find(c => c.id === candidaturaId)
    if (candidatura) {
      candidatura.company_rating = rating
    }
    
    applyFilters() // Reaplicar filtros após atualização
    console.log('Avaliação atualizada com sucesso!')
  } catch (error) {
    console.error('Erro ao atualizar avaliação:', error)
  }
}

const applyFilters = () => {
  let filtered = [...candidaturas.value]
  
  if (ratingFilter.value) {
    if (ratingFilter.value === 'unrated') {
      // Candidatos sem avaliação
      filtered = filtered.filter(c => !c.company_rating)
    } else {
      // Candidatos com avaliação >= valor selecionado
      const minRating = parseInt(ratingFilter.value)
      filtered = filtered.filter(c => c.company_rating && c.company_rating >= minRating)
    }
  }
  
  filteredCandidaturas.value = filtered
}

onMounted(() => {
  fetchCandidates()
  
  // Auto-refresh a cada 30 segundos
  refreshInterval = setInterval(() => {
    fetchCandidates()
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>
