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
                      <span class="ml-4 text-sm font-medium text-gray-500">Relatórios</span>
                    </div>
                  </li>
                </ol>
              </nav>
              <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                Relatórios da Oportunidade
              </h1>
              <p class="mt-1 text-sm text-gray-500">
                Analise o desempenho e engagement da sua oportunidade
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

      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin mx-auto h-8 w-8 text-blue-600">
          <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <p class="mt-2 text-gray-500">A carregar relatórios...</p>
      </div>

      <div v-else class="space-y-6">
        <!-- Overview Stats -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total de Visitas
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ reports.total_visitas || 0 }}
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
                  <svg class="h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Candidaturas
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ reports.total_candidaturas || 0 }}
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
                  <svg class="h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Candidaturas Este Mês
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ reports.candidaturas_mes || 0 }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Browsers Chart -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Browsers Utilizados
            </h3>
            <div v-if="Object.keys(reports.browsers || {}).length === 0" class="text-center py-8 text-gray-500">
              Nenhum dado de browser disponível
            </div>
            <div v-else class="space-y-3">
              <div v-for="(count, browser) in reports.browsers" :key="browser" class="flex items-center">
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-900">{{ browser }}</span>
                    <span class="text-sm text-gray-500">{{ count }} visitas</span>
                  </div>
                  <div class="mt-1 relative">
                    <div class="bg-gray-200 rounded-full h-2">
                      <div 
                        class="bg-blue-600 h-2 rounded-full" 
                        :style="{ width: getBarWidth(count) + '%' }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Temporal Visits Chart - Visitors Timeline -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Visitors Timeline
              </h3>
              <span class="text-sm text-gray-500">
                Last 30 days • Hoje: {{ formatDate(new Date().toISOString()) }}
              </span>
            </div>
            
            <div v-if="!reports.visitas_temporais || reports.visitas_temporais.length === 0" class="text-center py-8 text-gray-500">
              Nenhum dado temporal disponível
            </div>
            <div v-else class="mt-6">
              <div class="flow-root">
                <div class="h-64 flex items-end justify-between space-x-1 overflow-x-auto">
                  <div 
                    v-for="(visita, index) in reports.visitas_temporais" 
                    :key="index"
                    class="flex flex-col items-center group relative min-w-0 flex-shrink-0"
                  >
                    <div 
                      class="bg-blue-500 rounded-t-sm transition-colors group-hover:bg-blue-600 w-6"
                      :style="{ height: getBarHeight(visita.total || visita.visitas || 0) + 'px' }"
                    ></div>
                    <div class="mt-2 text-xs text-gray-500 text-center transform -rotate-45 w-16">
                      {{ formatChartDate(visita.data) }}
                    </div>
                    <!-- Tooltip -->
                    <div class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-10">
                      {{ visita.total || visita.visitas || 0 }} visitas em {{ formatDate(visita.data) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Visitor Locations -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Visitor Locations
            </h3>
            <div v-if="!reports.localizacoes || reports.localizacoes.length === 0" class="text-center py-8 text-gray-500">
              Nenhum dado de localização disponível
            </div>
            <div v-else>
              <!-- Mapa placeholder (pode ser implementado com Leaflet.js depois) -->
              <div class="bg-gray-100 rounded-lg h-64 mb-4 flex items-center justify-center">
                <div class="text-center">
                  <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <p class="mt-2 text-sm text-gray-500">Mapa de Visitantes</p>
                </div>
              </div>
              
              <!-- Lista de localizações -->
              <div class="overflow-hidden">
                <table class="min-w-full">
                  <thead>
                    <tr class="border-b border-gray-200">
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        País
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Cidade
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Visitas
                      </th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="location in reports.localizacoes" :key="`${location.country}-${location.city}`">
                      <td class="px-4 py-2 text-sm font-medium text-gray-900">
                        {{ location.country || 'Desconhecido' }}
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-500">
                        {{ location.city || 'Desconhecido' }}
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-500">
                        {{ location.total || location.count || 0 }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Candidaturas Temporais Chart -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Candidaturas por Dia (Últimos 30 dias)
            </h3>
            <div v-if="!reports.candidaturas_temporais || reports.candidaturas_temporais.length === 0" class="text-center py-8 text-gray-500">
              Nenhuma candidatura ainda
            </div>
            <div v-else class="mt-6">
              <div class="flow-root">
                <div class="h-64 flex items-end justify-between space-x-1">
                  <div 
                    v-for="(candidatura, index) in reports.candidaturas_temporais.slice(0, 15)" 
                    :key="index"
                    class="flex flex-col items-center group relative"
                  >
                    <div 
                      class="bg-green-500 rounded-t-sm transition-colors group-hover:bg-green-600 w-6"
                      :style="{ height: getCandidaturaBarHeight(candidatura.total) + 'px' }"
                    ></div>
                    <div class="mt-2 text-xs text-gray-500 text-center transform -rotate-45 w-16">
                      {{ formatChartDate(candidatura.data) }}
                    </div>
                    <!-- Tooltip -->
                    <div class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-1 px-2 whitespace-nowrap">
                      {{ candidatura.total }} candidatura(s) em {{ formatDate(candidatura.data) }}
                    </div>
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
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import OpportunityTabs from '@/components/OpportunityTabs.vue'

const route = useRoute()
const id = route.params.id as string

const loading = ref(true)
const reports = ref<any>({})

const conversionRate = computed(() => {
  const visitas = reports.value.total_visitas || 0
  const candidaturas = reports.value.total_candidaturas || 0
  return visitas > 0 ? ((candidaturas / visitas) * 100).toFixed(2) : '0.00'
})

const visitasHoje = computed(() => {
  const hoje = new Date().toISOString().split('T')[0]
  const visitaHoje = reports.value.visitas_temporais?.find((v: any) => v.data === hoje)
  return visitaHoje?.visitas || 0
})

const maxVisitas = computed(() => {
  if (!reports.value.visitas_temporais || reports.value.visitas_temporais.length === 0) {
    return 1
  }
  return Math.max(...reports.value.visitas_temporais.map((v: any) => v.total || v.visitas || 0))
})

const maxCandidaturas = computed(() => {
  if (!reports.value.candidaturas_temporais || reports.value.candidaturas_temporais.length === 0) {
    return 1
  }
  return Math.max(...reports.value.candidaturas_temporais.map((c: any) => c.total || 0))
})

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-PT', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatChartDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-PT', {
    month: 'short',
    day: 'numeric'
  })
}

const getBarWidth = (count: number) => {
  if (!reports.value.browsers || Object.keys(reports.value.browsers).length === 0) {
    return 0
  }
  const maxCount = Math.max(...Object.values(reports.value.browsers) as number[])
  return maxCount > 0 ? (count / maxCount) * 100 : 0
}

const getBarHeight = (visitas: number) => {
  return Math.max((visitas / maxVisitas.value) * 240, 4)
}

const getCandidaturaBarHeight = (candidaturas: number) => {
  return Math.max((candidaturas / maxCandidaturas.value) * 240, 4)
}

const fetchReports = async () => {
  try {
    const token = localStorage.getItem('company_token')
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/companies/oportunidades/${id}/reports`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'ngrok-skip-browser-warning': 'true'
      }
    })
    
    reports.value = response.data
  } catch (error) {
    console.error('Erro ao buscar relatórios:', error)
    // Dados de fallback em caso de erro
    reports.value = {
      total_visitas: 0,
      total_candidaturas: 0,
      visitas_temporais: [],
      browsers: {},
      localizacoes: []
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchReports()
})
</script>
