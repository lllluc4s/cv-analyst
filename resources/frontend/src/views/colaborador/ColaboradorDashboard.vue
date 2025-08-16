<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Dashboard do Colaborador</h2>
        
        <!-- Loading state -->
        <div v-if="loading" class="text-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-2 text-sm text-gray-600">Carregando dados...</p>
        </div>

        <!-- Dashboard content -->
        <div v-else-if="dashboard" class="space-y-6">
          <!-- Estatísticas -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium opacity-90">Total Candidaturas</p>
                  <p class="text-2xl font-bold">{{ dashboard.estatisticas.total_candidaturas }}</p>
                </div>
                <div class="text-3xl opacity-80">📋</div>
              </div>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium opacity-90">Contratações</p>
                  <p class="text-2xl font-bold">{{ dashboard.estatisticas.candidaturas_contratadas }}</p>
                </div>
                <div class="text-3xl opacity-80">✅</div>
              </div>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium opacity-90">Taxa de Sucesso</p>
                  <p class="text-2xl font-bold">{{ dashboard.estatisticas.taxa_sucesso }}%</p>
                </div>
                <div class="text-3xl opacity-80">📊</div>
              </div>
            </div>

            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-4 text-white">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium opacity-90">Contratos Ativos</p>
                  <p class="text-2xl font-bold">{{ dashboard.estatisticas.contratos_ativos }}</p>
                </div>
                <div class="text-3xl opacity-80">🏢</div>
              </div>
            </div>
          </div>

          <!-- Contratos Ativos -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Contratos Ativos</h3>
              
              <div v-if="dashboard.contratos_ativos.length === 0" class="text-center py-8">
                <div class="text-gray-400 text-5xl mb-4">💼</div>
                <p class="text-gray-600">Não há contratos ativos no momento.</p>
              </div>

              <div v-else class="space-y-4">
                <div 
                  v-for="contrato in dashboard.contratos_ativos" 
                  :key="contrato.id"
                  class="border rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <h4 class="font-medium text-gray-900">{{ contrato.posicao }}</h4>
                      <p class="text-sm text-gray-600">{{ contrato.empresa }}</p>
                      <p class="text-sm text-gray-500">{{ contrato.departamento }}</p>
                    </div>
                    <div class="text-right">
                      <p class="text-sm font-medium text-green-600">
                        {{ (contrato as any).sem_termo ? 'Sem termo' : 'Prazo determinado' }}
                      </p>
                      <p class="text-xs text-gray-500">
                        Desde {{ formatDate(contrato.data_inicio) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Perfil Resumido -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Perfil Profissional</h3>
              
              <div class="space-y-4">
                <div>
                  <h4 class="font-medium text-gray-900">{{ (dashboard as any).perfil_candidato?.nome || 'Nome não disponível' }}</h4>
                  <p class="text-sm text-gray-600">{{ (dashboard as any).perfil_candidato?.email || 'Email não disponível' }}</p>
                </div>

                <div v-if="(dashboard as any).perfil_candidato?.skills?.length > 0">
                  <h4 class="font-medium text-gray-900 mb-2">Competências</h4>
                  <div class="flex flex-wrap gap-2">
                    <span 
                      v-for="skill in (dashboard as any).perfil_candidato.skills.slice(0, 6)" 
                      :key="skill"
                      class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full"
                    >
                      {{ skill }}
                    </span>
                    <span 
                      v-if="(dashboard as any).perfil_candidato.skills.length > 6"
                      class="px-3 py-1 bg-gray-100 text-gray-600 text-sm rounded-full"
                    >
                      +{{ (dashboard as any).perfil_candidato.skills.length - 6 }} mais
                    </span>
                  </div>
                </div>

                <div>
                  <router-link 
                    to="/colaborador/perfil"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200"
                  >
                    Ver perfil completo
                  </router-link>
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
import colaboradorService, { type ColaboradorDashboard } from '@/services/colaboradorService'

const dashboard = ref<ColaboradorDashboard | null>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    dashboard.value = await colaboradorService.getDashboard()
  } catch (error) {
    console.error('Erro ao carregar dashboard:', error)
  } finally {
    loading.value = false
  }
})

const formatDate = (date: string) => {
  return colaboradorService.formatDate(date)
}
</script>
