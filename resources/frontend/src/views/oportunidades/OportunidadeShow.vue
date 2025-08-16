<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { oportunidadesService, type Oportunidade, type ReportData, type Candidatura } from '../../services/api'
import OpenStreetMapVisitors from '../../components/OpenStreetMapVisitors.vue'
import KanbanBoard from '../empresas/KanbanBoard.vue'
import { UrlBuilder } from '@/utils/urlBuilder'

// Define o URL base do backend
const BACKEND_URL = UrlBuilder.getApiBaseUrl()

// Estado para preview do CV
const previewCvUrl = ref<string | null>(null)
const showPreviewModal = ref(false)

// Solução simples: iframe direto para o PDF
function openPreviewCv(cvPath: string) {
  try {
    previewCvUrl.value = `${BACKEND_URL}/storage/${cvPath}`;
    showPreviewModal.value = true;
  } catch (e) {
    alert('Não foi possível carregar o PDF para preview.');
  }
}
function closePreviewCv() {
  showPreviewModal.value = false;
  previewCvUrl.value = null;
}

const route = useRoute()
const oportunidade = ref<Oportunidade | null>(null)
const reportData = ref<ReportData | null>(null)
const candidaturas = ref<Candidatura[]>([])
const loading = ref(true)
const loadingReports = ref(false)
const loadingCandidaturas = ref(false)
const error = ref('')
const activeTab = ref('detalhes')

// Controle de visualização do Kanban
const viewMode = ref<'list' | 'kanban'>('list')

// Modal para criar nova coluna/estado
const showCreateStateModal = ref(false)
const newState = ref({
  nome: '',
  cor: '#3B82F6',
  email_enabled: false,
  email_subject: '',
  email_body: ''
})

// Message state
const message = ref({
  show: false,
  text: '',
  type: 'success' as 'success' | 'error'
})

// Computed properties for better data handling
const chartData = computed(() => {
  if (!reportData.value?.visitas_por_dia) return []
  return reportData.value.visitas_por_dia.slice(-14)
})

const maxVisitas = computed(() => {
  if (chartData.value.length === 0) return 0
  const max = Math.max(...chartData.value.map(v => v.total))
  // Round up to next 5 or 10
  if (max <= 50) {
    return Math.ceil(max / 5) * 5
  } else {
    return Math.ceil(max / 10) * 10
  }
})

const yAxisLabels = computed(() => {
  if (maxVisitas.value === 0) return [0]
  const step = maxVisitas.value <= 50 ? 5 : 10
  const labels = []
  for (let i = maxVisitas.value; i >= 0; i -= step) {
    labels.push(i)
  }
  return labels
})

const hasChartData = computed(() => {
  return chartData.value.length > 0 && maxVisitas.value > 0
})

// Map helper functions
const getLocationPosition = (country: string) => {
  const positions: { [key: string]: string } = {
    'Portugal': 'left: 44%; top: 35%;',
    'Brasil': 'left: 25%; top: 60%;',
    'França': 'left: 47%; top: 32%;',
    'Alemanha': 'left: 50%; top: 28%;',
    'Espanha': 'left: 45%; top: 38%;',
    'Itália': 'left: 49%; top: 36%;',
    'Reino Unido': 'left: 43%; top: 25%;'
  }
  return positions[country] || 'left: 50%; top: 50%;'
}

const getLocationColor = (index: number, forList = false) => {
  const colors = [
    forList ? 'bg-blue-500' : 'bg-blue-500',
    forList ? 'bg-green-500' : 'bg-green-500', 
    forList ? 'bg-purple-500' : 'bg-purple-500',
    forList ? 'bg-orange-500' : 'bg-orange-500',
    forList ? 'bg-yellow-500' : 'bg-yellow-500'
  ]
  return colors[index] || (forList ? 'bg-gray-500' : 'bg-gray-500')
}

const loadOportunidade = async () => {
  try {
    loading.value = true
    error.value = ''
    const slug = route.params.slug as string
    oportunidade.value = await oportunidadesService.getBySlug(slug)
  } catch (err: any) {
    if (err.response?.status === 404) {
      error.value = 'Oportunidade não encontrada.'
    } else {
      error.value = 'Erro ao carregar oportunidade.'
    }
    console.error(err)
  } finally {
    loading.value = false
  }
}

const loadReports = async () => {
  if (!oportunidade.value?.slug) return
  
  try {
    loadingReports.value = true
    reportData.value = await oportunidadesService.getReports(oportunidade.value.slug)
    console.log('Report data loaded:', reportData.value)
    console.log('Visitas por dia:', reportData.value?.visitas_por_dia)
    // Log do último dia para debug
    if (reportData.value?.visitas_por_dia?.length) {
      const ultimoDia = reportData.value.visitas_por_dia[reportData.value.visitas_por_dia.length - 1]
      console.log('Último dia no relatório:', ultimoDia)
    }
  } catch (err) {
    console.error('Erro ao carregar reports:', err)
  } finally {
    loadingReports.value = false
  }
}

const loadCandidaturas = async () => {
  if (!oportunidade.value?.id) return
  
  try {
    loadingCandidaturas.value = true
    const response = await oportunidadesService.getCandidaturas(oportunidade.value.id)
    candidaturas.value = response.candidaturas
  } catch (err) {
    console.error('Erro ao carregar candidaturas:', err)
  } finally {
    loadingCandidaturas.value = false
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const formatDateTime = (dateString: string) => {
  return new Date(dateString).toLocaleString('pt-BR')
}

const setActiveTab = (tab: string) => {
  activeTab.value = tab
  if (tab === 'reports' && !reportData.value) {
    loadReports()
  } else if (tab === 'candidaturas' && candidaturas.value.length === 0) {
    loadCandidaturas()
  }
}

const setViewMode = (mode: 'list' | 'kanban') => {
  viewMode.value = mode
}

// Funções para criar novo estado
const showMessage = (text: string, type: 'success' | 'error' = 'success') => {
  message.value = { show: true, text, type }
  setTimeout(() => {
    message.value.show = false
  }, 3000)
}

const createState = async () => {
  if (!oportunidade.value?.id) {
    showMessage('Oportunidade não encontrada', 'error')
    return
  }

  try {
    const token = localStorage.getItem('company_token')
    if (!token) {
      showMessage('Token de autenticação não encontrado', 'error')
      return
    }
    
    // Adicionar campos obrigatórios de email se não estiverem presentes
    const stateData = {
      ...newState.value,
      email_enabled: newState.value.email_enabled || false,
      email_subject: newState.value.email_subject || '',
      email_body: newState.value.email_body || ''
    }
    
    const response = await fetch(`${import.meta.env.VITE_API_URL}/api/companies/kanban/oportunidades/${oportunidade.value.id}/states`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(stateData)
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Erro ao criar estado')
    }
    
    showCreateStateModal.value = false
    newState.value = { 
      nome: '', 
      cor: '#3B82F6',
      email_enabled: false,
      email_subject: '',
      email_body: ''
    }
    
    showMessage('Estado criado com sucesso!')
    
    // Recarregar candidaturas se estamos na view kanban
    if (viewMode.value === 'kanban') {
      await loadCandidaturas()
    }
  } catch (error: any) {
    console.error('Erro ao criar estado:', error)
    
    // Melhor tratamento de erro
    let errorMessage = 'Erro ao criar estado'
    if (error.message) {
      errorMessage = error.message
    }
    
    showMessage(errorMessage, 'error')
  }
}

onMounted(() => {
  loadOportunidade()
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <nav class="flex" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-4">
          <li>
            <div>
              <RouterLink to="/admin/oportunidades" class="text-gray-400 hover:text-gray-500">
                <svg class="flex-shrink-0 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
                <span class="sr-only">Home</span>
              </RouterLink>
            </div>
          </li>
          <li>
            <div class="flex items-center">
              <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
              <RouterLink to="/admin/oportunidades" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">
                Oportunidades
              </RouterLink>
            </div>
          </li>
          <li>
            <div class="flex items-center">
              <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
              <span class="ml-4 text-sm font-medium text-gray-500">{{ oportunidade?.titulo || 'Carregando...' }}</span>
            </div>
          </li>
        </ol>
      </nav>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
      <div class="flex">
        <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        <div>
          <h3 class="text-sm font-medium text-red-800">Erro</h3>
          <div class="mt-1 text-sm text-red-700">{{ error }}</div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-else-if="oportunidade" class="bg-white shadow rounded-lg">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ oportunidade.titulo }}</h1>
            <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
              <span>Criado em {{ formatDate(oportunidade.created_at!) }}</span>
              <span class="flex items-center">
                <div class="w-2 h-2 rounded-full mr-2" :class="oportunidade.ativa ? 'bg-green-400' : 'bg-red-400'"></div>
                {{ oportunidade.ativa ? 'Ativa' : 'Inativa' }}
              </span>
              <span class="flex items-center">
                <div class="w-2 h-2 rounded-full mr-2" :class="oportunidade.publica ? 'bg-blue-400' : 'bg-gray-400'"></div>
                {{ oportunidade.publica ? 'Pública' : 'Privada' }}
              </span>
            </div>
          </div>
          <div class="flex space-x-3">
            <RouterLink
              :to="`/admin/oportunidades/${oportunidade.slug}/edit`"
              class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </RouterLink>
            <a
              :href="`${BACKEND_URL}/oportunidade/${oportunidade.slug}`"
              target="_blank"
              class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg>
              Ver Página Pública
            </a>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
          <button
            @click="setActiveTab('detalhes')"
            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
            :class="{ 'border-blue-500 text-blue-600': activeTab === 'detalhes' }"
          >
            Detalhes
          </button>
          <button
            @click="setActiveTab('candidaturas')"
            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
            :class="{ 'border-blue-500 text-blue-600': activeTab === 'candidaturas' }"
          >
            Candidaturas
          </button>
          <button
            @click="setActiveTab('reports')"
            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
            :class="{ 'border-blue-500 text-blue-600': activeTab === 'reports' }"
          >
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Reports
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="p-6">
        <!-- Detalhes Tab -->
        <div v-if="activeTab === 'detalhes'">
          <div class="space-y-6">
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-3">Descrição</h3>
              <div class="prose max-w-none text-gray-700 whitespace-pre-wrap">{{ oportunidade.descricao }}</div>
            </div>

            <div v-if="oportunidade.skills_desejadas?.length">
              <h3 class="text-lg font-medium text-gray-900 mb-3">Skills Desejadas</h3>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="skill in oportunidade.skills_desejadas"
                  :key="skill.nome"
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                >
                  {{ skill.nome }}
                  <span v-if="skill.peso" class="ml-1 text-xs text-blue-600">({{ skill.peso }})</span>
                </span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <h4 class="text-sm font-medium text-gray-900">Localização</h4>
                <p class="mt-1 text-sm text-gray-600">{{ oportunidade.localizacao || 'Não especificada' }}</p>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-900">Slug</h4>
                <p class="mt-1 text-sm text-gray-600">{{ oportunidade.slug }}</p>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-900">Última Atualização</h4>
                <p class="mt-1 text-sm text-gray-600">{{ formatDate(oportunidade.updated_at!) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Candidaturas Tab -->
        <div v-else-if="activeTab === 'candidaturas'">
          <div v-if="loadingCandidaturas" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
          
          <div v-else-if="candidaturas.length > 0" class="space-y-6">
            <!-- Header com toggle de visualização -->
            <div class="flex items-center justify-between">
              <div class="bg-gray-50 p-6 rounded-lg flex-1">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Resumo</h3>
                <div class="bg-white p-4 rounded border inline-block">
                  <div class="flex items-center">
                    <svg class="h-6 w-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <div>
                      <p class="text-sm text-gray-600">Total de Candidaturas</p>
                      <p class="text-xl font-bold text-gray-900">{{ candidaturas.length }}</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Toggle de visualização -->
              <div class="ml-6">
                <div class="flex items-center bg-gray-100 rounded-lg p-1">
                  <button
                    @click="setViewMode('list')"
                    :class="[
                      'flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors',
                      viewMode === 'list'
                        ? 'bg-white text-gray-900 shadow-sm'
                        : 'text-gray-600 hover:text-gray-900'
                    ]"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    Lista
                  </button>
                  <button
                    @click="setViewMode('kanban')"
                    :class="[
                      'flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors',
                      viewMode === 'kanban'
                        ? 'bg-white text-gray-900 shadow-sm'
                        : 'text-gray-600 hover:text-gray-900'
                    ]"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2 2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                    Kanban
                  </button>
                </div>
              </div>
              
              <!-- Botão Adicionar Nova Coluna (apenas visível no Kanban) -->
              <div v-if="viewMode === 'kanban'" class="ml-4">
                <button
                  @click="showCreateStateModal = true"
                  class="inline-flex items-center px-4 py-2 border border-dashed border-indigo-300 rounded-lg text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 hover:border-indigo-400 transition-all duration-200"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Adicionar Nova Coluna
                </button>
              </div>
            </div>

            <!-- Visualização em Lista -->
            <div v-if="viewMode === 'list'" class="bg-white border border-gray-200 rounded-lg overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Candidaturas Recebidas</h3>
                <p class="text-sm text-gray-600 mt-1">Lista de todas as candidaturas para esta oportunidade</p>
              </div>
              
              <div class="divide-y divide-gray-200">
                <div v-for="candidatura in candidaturas" :key="candidatura.id" class="px-6 py-4 hover:bg-gray-50">
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <div class="flex-1">
                          <h4 class="text-sm font-medium text-gray-900">
                            {{ candidatura.nome }} ({{ candidatura.apelido }})
                          </h4>
                          <div class="flex items-center space-x-4 mt-1">
                            <span class="text-sm text-gray-600">{{ candidatura.email }}</span>
                            <span class="text-sm text-gray-600">{{ candidatura.telefone }}</span>
                            <span v-if="candidatura.linkedin" class="text-sm text-blue-600">
                              <a :href="candidatura.linkedin" target="_blank" class="hover:underline">LinkedIn</a>
                            </span>
                          </div>
                          <div class="mt-2">
                            <span class="text-xs text-gray-500">Candidatura enviada em {{ formatDateTime(candidatura.created_at!) }}</span>
                          </div>
                          
                          <!-- Skills Extraídas -->
                          <div class="mt-3">
                            <p class="text-xs text-gray-500 mb-1">Skills Identificadas:</p>
                            <div class="flex flex-wrap gap-1">
                              <span 
                                v-for="skill in candidatura.skills_extraidas?.slice(0, 8) || []" 
                                :key="skill"
                                class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full"
                              >
                                {{ skill }}
                              </span>
                              <span 
                                v-if="(candidatura.skills_extraidas?.length || 0) > 8"
                                class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full"
                              >
                                +{{ (candidatura.skills_extraidas?.length || 0) - 8 }} mais
                              </span>
                            </div>
                          </div>
                        </div>

                        <!-- Ações -->
                        <div class="ml-6 flex flex-col gap-2">
                          <button
                            v-if="candidatura.cv_path"
                            @click="openPreviewCv(candidatura.cv_path)"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors"
                          >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A2 2 0 0020 6.382V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2v-1.382a2 2 0 00-.447-1.342L15 14M10 9l2 2 4-4" />
                            </svg>
                            Preview
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Visualização em Kanban -->
            <div v-else-if="viewMode === 'kanban'">
              <KanbanBoard 
                v-if="oportunidade?.company_id"
                :companyId="oportunidade.company_id"
                :oportunidadeId="oportunidade.id"
                :hideHeader="true"
                class="min-h-screen"
              />
            </div>
          </div>
          
          <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma candidatura</h3>
            <p class="mt-1 text-sm text-gray-500">Esta oportunidade ainda não recebeu candidaturas.</p>
          </div>
        </div>

        <!-- Reports Tab -->
        <div v-else-if="activeTab === 'reports'">
          <div v-if="loadingReports" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
          
          <div v-else-if="reportData" class="space-y-8">
            <!-- Debug info -->
            <div v-if="reportData.visitas_por_dia.length === 0" class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
              <p class="text-sm text-yellow-800">Debug: Nenhum dado de visitas por dia encontrado. Total de visitas: {{ reportData.total_visitas }}</p>
            </div>
            
            <!-- KPI Cards - Similar to the reference -->
            <!-- KPI Cards removidos conforme solicitado -->

            <!-- Main Chart Area -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- Visitors Timeline Chart -->
              <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                  <h3 class="text-lg font-semibold text-gray-900">Visitors Timeline</h3>
                  <div class="text-sm text-gray-500">
                    Last 30 days • Hoje: {{ new Date().toLocaleDateString('pt-BR') }}
                  </div>
                </div>
                <div v-if="hasChartData" class="relative">
                  <!-- Chart Container -->
                  <div class="h-80 relative p-4">
                    <!-- Y-axis labels -->
                    <div class="absolute left-0 top-4 h-72 flex flex-col justify-between text-xs text-gray-500 pr-3">
                      <span v-for="label in yAxisLabels" :key="label">{{ label }}</span>
                    </div>
                    <!-- Chart area -->
                    <div class="ml-8 h-72 relative">
                      <!-- Bars -->
                      <div class="h-full flex items-end justify-between px-2 gap-1">
                        <div 
                          v-for="(visita, index) in chartData" 
                          :key="`bar-${visita.data}-${index}`"
                          class="flex-1 flex flex-col items-center group relative"
                        >
                          <div 
                            :class="[
                              'w-full rounded-t transition-all duration-300 cursor-pointer',
                              visita.data === new Date().toISOString().split('T')[0] 
                                ? 'bg-gradient-to-t from-green-500 to-green-400 hover:from-green-600 hover:to-green-500' 
                                : 'bg-gradient-to-t from-blue-500 to-blue-400 hover:from-blue-600 hover:to-blue-500'
                            ]"
                            :style="{ 
                              height: `${Math.max(4, (visita.total / maxVisitas) * 280)}px`
                            }"
                            :title="`${formatDate(visita.data)}: ${visita.total} visitas${visita.data === new Date().toISOString().split('T')[0] ? ' (hoje)' : ''}`"
                          >
                            <!-- Tooltip on hover -->
                            <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                              {{ formatDate(visita.data) }}: {{ visita.total }} visitas
                            </div>
                          </div>
                          <!-- Date labels below bars -->
                          <div :class="[
                            'text-xs mt-2 text-center transform -rotate-45 origin-center w-8',
                            visita.data === new Date().toISOString().split('T')[0] 
                              ? 'text-green-600 font-bold' 
                              : 'text-gray-500'
                          ]">
                            {{ visita.data === new Date().toISOString().split('T')[0] 
                                ? 'HOJE' 
                                : new Date(visita.data).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }) 
                            }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="h-64 flex items-center justify-center text-gray-500 bg-gray-50 rounded border">
                  <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p>No data available for the selected period</p>
                    <p class="text-xs mt-1">Total visits: {{ reportData?.total_visitas || 0 }}</p>
                  </div>
                </div>
              </div>
              <!-- Browsers and Map Side by Side -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8 col-span-2">
                <!-- Browsers -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                  <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Browsers</h3>
                  <div v-if="reportData.browsers_mais_usados.length" class="space-y-4">
                    <div 
                      v-for="(browser, index) in reportData.browsers_mais_usados.slice(0, 8)" 
                      :key="browser.browser"
                      class="flex items-center justify-between"
                    >
                      <div class="flex items-center space-x-3">
                        <!-- Browser icon -->
                        <div class="w-6 h-6 rounded bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                          <svg v-if="browser.browser === 'Chrome'" class="w-4 h-4 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z"/>
                          </svg>
                          <svg v-else-if="browser.browser === 'Firefox'" class="w-4 h-4 text-orange-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z"/>
                          </svg>
                          <svg v-else-if="browser.browser === 'Safari'" class="w-4 h-4 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z"/>
                          </svg>
                          <svg v-else-if="browser.browser === 'Edge'" class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z"/>
                          </svg>
                          <svg v-else class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                          </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ browser.browser }}</span>
                      </div>
                      <div class="flex items-center space-x-3">
                        <div class="w-20 bg-gray-200 rounded-full h-2">
                          <div 
                            class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300"
                            :style="{ width: `${(browser.total / reportData.browsers_mais_usados[0].total) * 100}%` }"
                          ></div>
                        </div>
                        <span class="text-sm font-semibold text-gray-900 w-8 text-right">{{ browser.total }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center py-8 text-gray-500">
                    No browser data available
                  </div>
                </div>
                <!-- Top Locations Map -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mt-8 md:mt-0">
                  <h3 class="text-lg font-semibold text-gray-900 mb-4">Visitor Locations</h3>
                  <!-- OpenStreetMap Component -->
                  <div class="mb-4">
                    <OpenStreetMapVisitors 
                      :locations="reportData?.visitas_por_cidade?.length ? 
                        reportData.visitas_por_cidade.slice(0, 5).map((local, index) => ({
                          city: local.city,
                          region: local.region || '',
                          country: local.country,
                          latitude: local.latitude || 0,
                          longitude: local.longitude || 0,
                          visits: local.total
                        })) : []"
                    />
                  </div>
                  <!-- Lista de cidades removida, exibe apenas o mapa -->
                </div>
              </div>
              
              <!-- Social Shares Statistics -->
              <div v-if="(reportData as any)?.partilhas_por_plataforma?.length" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                  </svg>
                  Partilhas Sociais
                  <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                    {{ (reportData as any)?.total_partilhas || 0 }}
                  </span>
                </h3>
                <div class="space-y-3">
                  <div 
                    v-for="plataforma in (reportData as any)?.partilhas_por_plataforma" 
                    :key="plataforma.platform"
                    class="flex items-center justify-between"
                  >
                    <div class="flex items-center space-x-3">
                      <!-- Platform icon -->
                      <div class="w-6 h-6 rounded bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                        <svg v-if="plataforma.platform === 'facebook'" class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <svg v-else-if="plataforma.platform === 'twitter'" class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                        <svg v-else-if="plataforma.platform === 'linkedin'" class="w-4 h-4 text-blue-700" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                        <svg v-else-if="plataforma.platform === 'whatsapp'" class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                        </svg>
                        <svg v-else-if="plataforma.platform === 'copy_link'" class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <svg v-else class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                        </svg>
                      </div>
                      <span class="text-sm font-medium text-gray-900 capitalize">
                        {{ plataforma.platform === 'copy_link' ? 'Copiar Link' : plataforma.platform }}
                      </span>
                    </div>
                    <div class="flex items-center space-x-3">
                      <div class="w-20 bg-gray-200 rounded-full h-2">
                        <div 
                          class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full transition-all duration-300"
                          :style="{ width: `${(plataforma.total / ((reportData as any)?.total_partilhas || 1)) * 100}%` }"
                        ></div>
                      </div>
                      <span class="text-sm font-semibold text-gray-900 w-8 text-right">{{ plataforma.total }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

              <!-- Bottom Section: Browsers (Traffic Sources card removido) -->
              <!-- Último card de Top Browsers removido -->
          </div>
          
          <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum dado de relatório</h3>
            <p class="mt-1 text-sm text-gray-500">Os dados de visitas aparecerão aqui quando a página pública for acessada.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Preview do CV -->
    <div v-if="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-lg shadow-lg max-w-3xl w-full max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center p-4 border-b">
          <h3 class="text-lg font-semibold">Preview do CV</h3>
          <button @click="closePreviewCv" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
        </div>
        <div class="flex-1 overflow-auto">
          <iframe
            v-if="previewCvUrl"
            :src="previewCvUrl"
            class="w-full h-[70vh]"
            frameborder="0"
            style="min-height: 500px"
          ></iframe>
        </div>
      </div>
    </div>

    <!-- Modal de Criar Nova Coluna/Estado -->
    <div v-if="showCreateStateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Criar Nova Coluna</h3>
          <form @submit.prevent="createState">
            <div class="space-y-4">
              <!-- Nome do Estado -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Estado</label>
                <input
                  v-model="newState.nome"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  placeholder="Ex: Teste Técnico"
                >
              </div>
              
              <!-- Cor -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cor</label>
                <div class="flex space-x-2">
                  <input
                    v-model="newState.cor"
                    type="color"
                    class="w-12 h-10 border border-gray-300 rounded cursor-pointer"
                  >
                  <input
                    v-model="newState.cor"
                    type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="#3B82F6"
                  >
                </div>
              </div>

              <!-- Email Automático -->
              <div class="flex items-center">
                <input
                  id="new-email-enabled"
                  v-model="newState.email_enabled"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <label for="new-email-enabled" class="ml-3 block text-sm font-medium text-gray-700">
                  Enviar email automático quando candidato entrar neste estado
                </label>
              </div>

              <!-- Configurações de Email -->
              <div v-if="newState.email_enabled" class="space-y-3 bg-gray-50 p-3 rounded-md">
                <div>
                  <label for="new-email-subject" class="block text-sm font-medium text-gray-700">
                    Assunto do Email
                  </label>
                  <input
                    id="new-email-subject"
                    v-model="newState.email_subject"
                    type="text"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ex: Atualização sobre sua candidatura em {oportunidade}"
                  >
                </div>

                <div>
                  <label for="new-email-body" class="block text-sm font-medium text-gray-700">
                    Corpo do Email
                  </label>
                  <textarea
                    id="new-email-body"
                    v-model="newState.email_body"
                    rows="4"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ex: Olá {nome}, sua candidatura para {oportunidade} teve o status atualizado..."
                  ></textarea>
                  
                  <!-- Placeholders disponíveis -->
                  <div class="mt-2 text-sm text-gray-500">
                    <p class="font-medium">Placeholders disponíveis:</p>
                    <p><code class="bg-gray-100 px-1 rounded">{nome}</code> - Nome do candidato</p>
                    <p><code class="bg-gray-100 px-1 rounded">{oportunidade}</code> - Título da oportunidade</p>
                    <p><code class="bg-gray-100 px-1 rounded">{empresa}</code> - Nome da empresa</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="showCreateStateModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="!newState.nome || !newState.cor"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
              >
                Criar Estado
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="message.show" class="fixed bottom-4 right-4 z-50">
      <div 
        :class="[
          'px-4 py-3 rounded-md shadow-lg',
          message.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        ]"
      >
        {{ message.text }}
      </div>
    </div>
  </div>
</template>
