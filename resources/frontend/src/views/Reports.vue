<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Reports e Tendências</h1>
        <p class="mt-2 text-gray-600">
          Análise de tendências de candidaturas e skills mais procuradas
        </p>
      </div>

      <!-- Filtro por Período -->
      <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Filtro por Período</h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Data Início
              </label>
              <input
                type="date"
                v-model="filtros.dataInicio"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Data Fim
              </label>
              <input
                type="date"
                v-model="filtros.dataFim"
                :min="filtros.dataInicio"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <div class="flex space-x-2">
              <button
                @click="aplicarFiltros"
                :disabled="loading"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ loading ? 'Carregando...' : 'Aplicar' }}
              </button>
              <button
                @click="limparFiltros"
                :disabled="loading"
                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Limpar
              </button>
            </div>
          </div>
          
          <!-- Indicador de filtro ativo -->
          <div v-if="filtroAtivo" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
            <div class="flex items-center">
              <svg class="h-5 w-5 text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="text-sm text-blue-700">
                Filtro ativo: {{ formatarPeriodoFiltro() }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-700">{{ error }}</p>
      </div>

      <!-- Content -->
      <div v-else-if="data" class="space-y-8">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 3a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total de Candidaturas</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ data.total_candidaturas }}</dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Skills Diferentes</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ Object.keys(data.top_skills).length }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Candidaturas por Mês -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Candidaturas por Mês</h3>
          </div>
          <div class="p-6">
            <div v-if="data.candidaturas_por_mes.length === 0" class="text-center text-gray-500 py-8">
              Nenhuma candidatura encontrada no período selecionado.
            </div>
            <div v-else class="space-y-4">
              <div v-for="(item, index) in data.candidaturas_por_mes" :key="item.mes" class="flex items-center">
                <div class="w-24 text-sm text-gray-600">{{ formatMes(item.mes) }}</div>
                <div class="flex-1 mx-4">
                  <div class="bg-gray-200 rounded-full h-4">
                    <div 
                      class="bg-blue-600 h-4 rounded-full transition-all duration-500"
                      :style="{ 'min-width': getPercentage(item.total_candidaturas, maxCandidaturas) }"
                    ></div>
                  </div>
                </div>
                <div class="w-12 text-sm font-medium text-gray-900 text-right">{{ item.total_candidaturas }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top 3 Skills -->
        <div class="bg-white rounded-lg shadow mt-6">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Top 3 Skills Mais Encontradas</h3>
          </div>
          <div class="p-6">
            <div v-if="Object.keys(data.top_skills).length === 0" class="text-center text-gray-500 py-8">
              Nenhuma skill encontrada no período selecionado.
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div v-for="(count, skill, index) in top3SkillsLimited" :key="skill" class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                  <span class="text-sm font-medium text-blue-600">{{ index + 1 }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate capitalize">{{ skill }}</p>
                </div>
                <div class="flex-shrink-0">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ count }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Skills por Mês (Top 5) -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Evolução dos Top 5 Skills</h3>
          </div>
          <div class="p-6">
            <div v-if="top5Skills.length === 0" class="text-center text-gray-500 py-8">
              Nenhuma skill encontrada no período selecionado.
            </div>
            <div v-else class="space-y-6">
              <div v-for="skill in top5Skills" :key="skill" class="border rounded-lg p-4">
                <h4 class="text-md font-medium text-gray-900 capitalize mb-3">{{ skill }}</h4>
                <div class="space-y-2">
                  <div v-for="(count, mes) in getSkillEvolution(skill)" :key="mes" class="flex items-center">
                    <div class="w-20 text-sm text-gray-600">{{ formatMes(mes) }}</div>
                    <div class="flex-1 mx-4">
                      <div class="bg-gray-200 rounded-full h-3">
                        <div 
                          class="bg-green-500 h-3 rounded-full transition-all duration-500"
                          :style="{ 'min-width': getPercentage(count, getMaxSkillCount(skill)) }"
                        ></div>
                      </div>
                    </div>
                    <div class="w-8 text-sm font-medium text-gray-900 text-right">{{ count }}</div>
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
import { reportsApi } from '@/services/api'

interface ReportsData {
  candidaturas_por_mes: Array<{ mes: string; total_candidaturas: number }>
  top_skills: Record<string, number>
  skills_por_mes: Record<string, Record<string, number>>
  total_candidaturas: number
  periodo: { inicio: string; fim: string }
}

const loading = ref(true)
const error = ref<string | null>(null)
const data = ref<ReportsData | null>(null)

// Filtros
const filtros = ref({
  dataInicio: '',
  dataFim: ''
})

const filtroAtivo = computed(() => {
  return filtros.value.dataInicio || filtros.value.dataFim
})

const maxCandidaturas = computed(() => {
  if (!data.value) return 0
  return Math.max(...data.value.candidaturas_por_mes.map(item => item.total_candidaturas))
})

const topSkillsLimited = computed(() => {
  if (!data.value) return {}
  return Object.fromEntries(Object.entries(data.value.top_skills).slice(0, 20))
})

// Novo computed para Top 3 Skills
const top3SkillsLimited = computed(() => {
  if (!data.value) return {}
  return Object.fromEntries(Object.entries(data.value.top_skills).slice(0, 3))
})

const top5Skills = computed(() => {
  if (!data.value) return []
  return Object.keys(data.value.top_skills).slice(0, 5)
})

const formatMes = (mes: string) => {
  const [ano, month] = mes.split('-')
  const meses = [
    'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
    'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'
  ]
  return `${meses[parseInt(month) - 1]} ${ano}`
}

const formatPeriodo = () => {
  if (!data.value?.periodo.inicio) return 'N/A'
  const inicio = new Date(data.value.periodo.inicio).toLocaleDateString('pt-BR')
  const fim = new Date(data.value.periodo.fim).toLocaleDateString('pt-BR')
  return `${inicio} - ${fim}`
}

const formatarPeriodoFiltro = () => {
  const parts = []
  if (filtros.value.dataInicio) {
    parts.push(`de ${new Date(filtros.value.dataInicio).toLocaleDateString('pt-BR')}`)
  }
  if (filtros.value.dataFim) {
    parts.push(`até ${new Date(filtros.value.dataFim).toLocaleDateString('pt-BR')}`)
  }
  return parts.join(' ')
}

const getPercentage = (value: number, max: number) => {
  return max > 0 ? `${(value / max) * 100}%` : '0%'
}

const getSkillEvolution = (skill: string) => {
  if (!data.value) return {}
  const evolution: Record<string, number> = {}
  
  for (const [mes, skills] of Object.entries(data.value.skills_por_mes)) {
    evolution[mes] = skills[skill] || 0
  }
  
  return evolution
}

const getMaxSkillCount = (skill: string) => {
  const evolution = getSkillEvolution(skill)
  return Math.max(...Object.values(evolution))
}

const loadData = async () => {
  try {
    loading.value = true
    error.value = null
    
    const filtrosApi: { data_inicio?: string; data_fim?: string } = {}
    if (filtros.value.dataInicio) {
      filtrosApi.data_inicio = filtros.value.dataInicio
    }
    if (filtros.value.dataFim) {
      filtrosApi.data_fim = filtros.value.dataFim
    }
    
    data.value = await reportsApi.getTendencias(Object.keys(filtrosApi).length > 0 ? filtrosApi : undefined)
  } catch (err) {
    console.error('Erro ao carregar dados de reports:', err)
    error.value = 'Erro ao carregar dados de reports. Tente novamente.'
  } finally {
    loading.value = false
  }
}

const aplicarFiltros = async () => {
  await loadData()
}

const limparFiltros = async () => {
  filtros.value.dataInicio = ''
  filtros.value.dataFim = ''
  await loadData()
}

onMounted(() => {
  loadData()
})
</script>
