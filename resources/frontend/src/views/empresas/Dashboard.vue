<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Tabs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'dashboard'"
            :class="[
              'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'dashboard'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Dashboard
          </button>
          <button
            @click="activeTab = 'oportunidades'"
            :class="[
              'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'oportunidades'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Oportunidades
          </button>
          <button
            @click="activeTab = 'colaboradores'"
            :class="[
              'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'colaboradores'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            👥 Colaboradores
          </button>
          <button
            @click="goToDiasNaoTrabalhados"
            :class="[
              'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            📅 Dias Não Trabalhados
          </button>
          <button
            @click="goToProfile"
            :class="[
              'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Perfil
          </button>
          <button
            @click="goToMessages"
            :class="[
              'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
              'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            💬 Mensagens
          </button>
        </nav>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Dashboard Tab -->
      <div v-if="activeTab === 'dashboard'" class="space-y-6">
        <div v-if="loading" class="text-center py-8">
          <div class="animate-spin mx-auto h-8 w-8 text-blue-600">
            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          <p class="mt-2 text-gray-500">A carregar dados...</p>
        </div>

        <div v-else>
          <!-- Stats -->
          <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-gray-500 truncate">
                        Total de Oportunidades
                      </dt>
                      <dd class="text-lg font-medium text-gray-900">
                        {{ dashboardData?.stats.total_oportunidades || 0 }}
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-gray-500 truncate">
                        Oportunidades Ativas
                      </dt>
                      <dd class="text-lg font-medium text-gray-900">
                        {{ dashboardData?.stats.oportunidades_ativas || 0 }}
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-sm font-medium text-gray-500 truncate">
                        Total de Candidaturas
                      </dt>
                      <dd class="text-lg font-medium text-gray-900">
                        {{ dashboardData?.stats.total_candidaturas || 0 }}
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Candidates -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                Candidaturas Recentes
              </h3>
              <div v-if="dashboardData?.recent_candidaturas && dashboardData.recent_candidaturas.length > 0" class="overflow-hidden">
                <ul class="divide-y divide-gray-200">
                  <li v-for="candidatura in dashboardData.recent_candidaturas" :key="candidatura.id" class="py-4">
                    <div class="flex items-center space-x-4">
                      <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                          <span class="text-sm font-medium text-blue-600">
                            {{ candidatura.nome.charAt(0).toUpperCase() }}
                          </span>
                        </div>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                          {{ candidatura.nome }}
                        </p>
                        <p class="text-sm text-gray-500 truncate">
                          {{ candidatura.oportunidade_titulo }}
                        </p>
                      </div>
                      <div class="flex-shrink-0">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          {{ candidatura.score }}% compatibilidade
                        </span>
                      </div>
                      <div class="flex-shrink-0 text-sm text-gray-500">
                        {{ formatDate(candidatura.created_at) }}
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
              <div v-else class="text-center py-4 text-gray-500">
                Ainda não tem candidaturas
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Oportunidades Tab -->
      <div v-if="activeTab === 'oportunidades'">
        <CompanyOportunidades />
      </div>

      <!-- Colaboradores Tab -->
      <div v-if="activeTab === 'colaboradores'">
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-green-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <h3 class="text-lg font-medium text-gray-900 mb-2">📋 Gestão de Colaboradores & Contratos</h3>
              <p class="text-gray-600 mb-6">Visualize todos os colaboradores contratados e gere contratos de trabalho em formato DOCX.</p>
              <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button
                  @click="$router.push('/empresas/colaboradores')"
                  class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  Ver Todos os Colaboradores
                </button>
                <div class="text-sm text-gray-500">
                  <p class="mb-2">💼 <strong>Funcionalidades disponíveis:</strong></p>
                  <ul class="text-left list-disc list-inside space-y-1">
                    <li>📄 Gerar contratos individuais ou em lote</li>
                    <li>👥 Visualizar detalhes de cada colaborador</li>
                    <li>🏢 Filtrar por posição e departamento</li>
                    <li>📊 Gestão completa de dados contratuais</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Perfil Tab -->
      <div v-if="activeTab === 'perfil'">
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-blue-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <h3 class="text-lg font-medium text-gray-900 mb-2">Gerir Perfil da Empresa</h3>
              <p class="text-gray-600 mb-6">Actualize as informações da sua empresa e logótipo numa página dedicada.</p>
              <button
                @click="goToProfile"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Ir para Perfil
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import CompanyOportunidades from '../../components/CompanyOportunidades.vue'

const router = useRouter()

// Reactive data
const activeTab = ref('dashboard')
const loading = ref(true)
const dashboardData = ref<any>(null)
const errorMessage = ref('')

// Helper functions
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-PT', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Methods
const goToProfile = () => {
  router.push('/empresas/perfil')
}

const goToDiasNaoTrabalhados = () => {
  router.push('/empresas/dias-nao-trabalhados')
}

const goToMessages = () => {
  router.push('/empresas/mensagens')
}

const fetchDashboardData = async () => {
  try {
    loading.value = true
    
    const token = localStorage.getItem('company_token')
    if (!token) {
      router.push('/empresas/login')
      return
    }

    const response = await fetch('/api/company/dashboard', {
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
      throw new Error('Erro ao carregar dados do dashboard')
    }

    const data = await response.json()
    dashboardData.value = data

  } catch (error) {
    console.error('Erro ao carregar dashboard:', error)
    errorMessage.value = 'Erro ao carregar dados do dashboard'
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchDashboardData()
})
</script>
