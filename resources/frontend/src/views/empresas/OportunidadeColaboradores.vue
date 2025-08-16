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
                      <span class="ml-4 text-sm font-medium text-gray-500">{{ oportunidade?.titulo }}</span>
                    </div>
                  </li>
                </ol>
              </nav>
              <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                Colaboradores Contratados
              </h1>
              <p class="mt-1 text-sm text-gray-500">
                Candidatos contratados através desta oportunidade
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
        <p class="mt-2 text-gray-500">A carregar colaboradores...</p>
      </div>

      <div v-else>
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-6">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Colaboradores Contratados
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ colaboradores.length }}
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
                  <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Salário Total Mensal
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      €{{ formatSalary(salarioTotalMensal) }}
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
                  <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Salário Médio
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      €{{ formatSalary(salarioMedio) }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Aniversários do Dia -->
        <div v-if="aniversariosHoje.length > 0" class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-6 mb-6">
          <div class="flex items-center mb-4">
            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
              <span class="text-lg">🎂</span>
            </div>
            <h3 class="text-lg font-semibold text-purple-800">Aniversários de Hoje</h3>
            <span class="ml-2 bg-purple-100 text-purple-700 text-xs font-medium px-2.5 py-0.5 rounded-full">
              {{ aniversariosHoje.length }}
            </span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="colaborador in aniversariosHoje"
              :key="colaborador.id"
              class="bg-white rounded-lg p-4 shadow-sm border border-purple-100 hover:shadow-md transition-shadow cursor-pointer"
              @click="openColaboradorModal(colaborador)"
            >
              <div class="flex items-center">
                <div class="flex-shrink-0 relative">
                  <div class="h-12 w-12 bg-gray-200 rounded-full flex items-center justify-center">
                    <img
                      v-if="colaborador.foto_url"
                      :src="colaborador.foto_url"
                      :alt="colaborador.candidatura.nome"
                      class="h-12 w-12 rounded-full object-cover"
                    />
                    <span v-else class="text-lg font-semibold text-gray-600">
                      {{ getInitials(colaborador.candidatura.nome) }}
                    </span>
                  </div>
                  <div class="absolute -top-1 -right-1 w-6 h-6 bg-purple-500 rounded-full flex items-center justify-center">
                    <span class="text-white text-xs">🎉</span>
                  </div>
                </div>
                <div class="ml-4 flex-1">
                  <h4 class="text-sm font-medium text-gray-900">
                    {{ colaborador.candidatura.nome }}
                  </h4>
                  <p class="text-xs text-gray-500">{{ colaborador.posicao }}</p>
                  <div class="flex items-center mt-1">
                    <span class="text-xs text-purple-600 font-medium">
                      {{ getIdade(colaborador.data_nascimento) }} anos
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="colaboradores.length === 0" class="text-center py-12">
          <div class="mx-auto h-12 w-12 text-gray-400">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
          </div>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum colaborador contratado</h3>
          <p class="mt-1 text-sm text-gray-500">
            Ainda não contratou nenhum candidato para esta oportunidade.
          </p>
          <div class="mt-6">
            <router-link
              :to="`/empresas/oportunidades/${id}/candidatos`"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
            >
              Ver Candidatos
            </router-link>
          </div>
        </div>

        <!-- Colaboradores Grid -->
        <div v-else>
          <!-- Header da seção -->
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-medium text-gray-900">Colaboradores Contratados</h3>
              <p class="text-sm text-gray-500">{{ colaboradores.length }} colaborador(es) ativo(s)</p>
            </div>
            <div class="flex space-x-3">
              <button
                v-if="colaboradores.length > 0"
                @click="irParaDiasNaoTrabalhados"
                class="px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 flex items-center"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Dias Não Trabalhados
              </button>
              <button
                v-if="colaboradores.length > 0"
                @click="gerarTodosContratos"
                :disabled="gerandoTodosContratos"
                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 flex items-center"
              >
                <svg v-if="gerandoTodosContratos" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                {{ gerandoTodosContratos ? 'Gerando...' : 'Baixar Todos os Contratos' }}
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="colaborador in colaboradores"
              :key="colaborador.id"
              class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow cursor-pointer border-l-4"
              :class="colaborador.data_fim_contrato ? 'border-l-orange-400' : 'border-l-green-400'"
              @click="openColaboradorModal(colaborador)"
            >
              <div class="p-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="h-12 w-12 bg-gray-200 rounded-full flex items-center justify-center">
                      <span class="text-lg font-semibold text-gray-600">
                        {{ getInitials(colaborador.candidatura.nome) }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4 flex-1 min-w-0">
                    <h3 class="text-lg font-medium text-gray-900 truncate">
                      {{ colaborador.candidatura.nome }}
                    </h3>
                    <p class="text-sm text-gray-500 truncate">
                      {{ colaborador.posicao }}
                    </p>
                    <p class="text-sm text-gray-500">
                      {{ colaborador.departamento }}
                    </p>
                  </div>
                </div>
                <div class="mt-4">
                  <div class="flex items-center justify-between text-sm text-gray-500">
                    <span>Contratado em:</span>
                    <span>{{ formatDate(colaborador.data_contratacao) }}</span>
                  </div>
                  <div class="flex items-center justify-between text-sm text-gray-500 mt-1">
                    <span>Salário:</span>
                    <span class="font-semibold text-green-600">€{{ formatSalary(colaborador.salario) }}</span>
                  </div>
                  <div class="flex items-center justify-between text-sm mt-2">
                    <span class="text-gray-500">Contrato:</span>
                    <span :class="colaborador.data_fim_contrato ? 'text-orange-600 bg-orange-100' : 'text-green-600 bg-green-100'" 
                          class="px-2 py-1 rounded-full text-xs font-medium">
                      {{ colaborador.data_fim_contrato ? '📅 Prazo determinado' : '♾️ Prazo indeterminado' }}
                    </span>
                  </div>
                </div>
                <!-- Botão rápido de contrato -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                  <button
                    @click.stop="gerarContrato(colaborador.id)"
                    class="w-full text-center text-sm text-blue-600 hover:text-blue-800 font-medium"
                  >
                    📄 Baixar Contrato
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Colaborador Detail Modal -->
    <div
      v-if="selectedColaborador"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click="closeColaboradorModal"
    >
      <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">
            Detalhes do Colaborador
          </h3>
          <button
            @click="closeColaboradorModal"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Informações Pessoais -->
          <div>
            <h4 class="text-md font-medium text-gray-900 mb-3">Informações Pessoais</h4>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-500">Nome Completo</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedColaborador.candidatura.nome }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Email</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedColaborador.candidatura.email }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Telefone</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedColaborador.candidatura.telefone }}</p>
              </div>
            </div>
          </div>

          <!-- Informações Profissionais -->
          <div>
            <h4 class="text-md font-medium text-gray-900 mb-3">Informações Profissionais</h4>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-500">Posição</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedColaborador.posicao }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Departamento</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedColaborador.departamento }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Salário</label>
                <p class="mt-1 text-sm text-gray-900 font-semibold text-green-600">
                  €{{ formatSalary(selectedColaborador.salario) }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Data de Contratação</label>
                <p class="mt-1 text-sm text-gray-900">{{ formatDate(selectedColaborador.data_contratacao) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Gestão de Contrato -->
        <div class="mt-6 pt-6 border-t border-gray-200">
          <h4 class="text-md font-medium text-gray-900 mb-4">📋 Gestão de Contrato</h4>
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900">Status do Contrato</p>
                  <p class="text-sm text-green-600">✅ Ativo</p>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                  <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900">Duração</p>
                  <p class="text-sm text-gray-600">
                    {{ selectedColaborador.data_fim_contrato ? 'Prazo determinado' : 'Prazo indeterminado' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex space-x-3">
            <button
              @click="gerarContrato(selectedColaborador.id)"
              :disabled="gerandoContrato"
              class="flex-1 px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50 flex items-center justify-center"
            >
              <svg v-if="gerandoContrato" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              {{ gerandoContrato ? 'Gerando Contrato...' : 'Baixar Contrato (DOCX)' }}
            </button>
            <button
              @click="editarColaborador(selectedColaborador)"
              class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 flex items-center"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Editar Dados
            </button>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 pt-6 border-t border-gray-200 flex justify-end">
          <button
            @click="closeColaboradorModal"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Fechar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import OpportunityTabs from '@/components/OpportunityTabs.vue'

const route = useRoute()
const router = useRouter()
const id = route.params.id as string

// State
const loading = ref(true)
const oportunidade = ref<any>(null)
const colaboradores = ref<any[]>([])
const selectedColaborador = ref<any>(null)
const gerandoContrato = ref(false)
const gerandoTodosContratos = ref(false)

// Computed
const salarioTotalMensal = computed(() => {
  return colaboradores.value.reduce((total, c) => total + (c.salario || 0), 0)
})

const salarioMedio = computed(() => {
  if (colaboradores.value.length === 0) return 0
  return salarioTotalMensal.value / colaboradores.value.length
})

const aniversariosHoje = computed(() => {
  const hoje = new Date()
  const diaHoje = hoje.getDate()
  const mesHoje = hoje.getMonth() + 1 // JavaScript months are 0-indexed
  
  console.log('Debug aniversariosHoje:', {
    hoje: hoje.toISOString(),
    diaHoje,
    mesHoje,
    colaboradores: colaboradores.value.length,
    colaboradoresData: colaboradores.value
  })
  
  const result = colaboradores.value.filter(c => {
    console.log('Verificando colaborador completo:', c)
    
    if (!c.data_nascimento) {
      console.log('  - Sem data_nascimento')
      return false
    }
    
    // Parse da data usando apenas a parte da data (ignorando timezone)
    const dataString = c.data_nascimento.split('T')[0] // "1989-07-10"
    const [ano, mes, dia] = dataString.split('-').map(Number)
    
    console.log('Verificando colaborador:', {
      nome: c.candidatura?.nome,
      dataNascimento: c.data_nascimento,
      dataStringParsed: dataString,
      anoNascimento: ano,
      mesNascimento: mes,
      diaNascimento: dia,
      diaHoje,
      mesHoje,
      matches: dia === diaHoje && mes === mesHoje
    })
    
    return dia === diaHoje && mes === mesHoje
  })
  
  console.log('Aniversariantes hoje (final):', result.length, result)
  return result
})

// Methods
const fetchOportunidade = async () => {
  try {
    const token = localStorage.getItem('company_token')
    
    if (!token) {
      router.push('/empresas/login')
      return
    }

    const response = await fetch(`${import.meta.env.VITE_API_URL}/api/company/oportunidades/${id}`, {
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
      throw new Error('Erro ao carregar oportunidade')
    }

    const data = await response.json()
    oportunidade.value = data.data

  } catch (error) {
    console.error('Erro ao carregar oportunidade:', error)
  }
}

const fetchColaboradores = async () => {
  try {
    const token = localStorage.getItem('company_token')
    
    if (!token) {
      router.push('/empresas/login')
      return
    }

    console.log('Fetching colaboradores from:', `${import.meta.env.VITE_API_URL}/api/colaboradores?oportunidade_id=${id}`)

    const response = await fetch(`${import.meta.env.VITE_API_URL}/api/colaboradores?oportunidade_id=${id}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })

    if (!response.ok) {
      console.error('Response not ok:', response.status, response.statusText)
      if (response.status === 401) {
        localStorage.removeItem('company_token')
        router.push('/empresas/login')
        return
      }
      throw new Error('Erro ao carregar colaboradores')
    }

    const data = await response.json()
    console.log('Colaboradores response:', data)
    colaboradores.value = data.data || []
    console.log('Colaboradores set to:', colaboradores.value)

  } catch (error) {
    console.error('Erro ao carregar colaboradores:', error)
  } finally {
    loading.value = false
  }
}

const openColaboradorModal = (colaborador: any) => {
  selectedColaborador.value = colaborador
}

const closeColaboradorModal = () => {
  selectedColaborador.value = null
}

const getInitials = (name: string) => {
  if (!name) return '??'
  const names = name.split(' ')
  if (names.length === 1) return names[0].substring(0, 2).toUpperCase()
  return (names[0][0] + names[names.length - 1][0]).toUpperCase()
}

const formatDate = (dateString: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('pt-PT', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatSalary = (salary: number) => {
  if (!salary) return '0'
  return new Intl.NumberFormat('pt-PT').format(salary)
}

const gerarContrato = async (colaboradorId: number) => {
  try {
    gerandoContrato.value = true
    const token = localStorage.getItem('company_token')
    
    if (!token) {
      router.push('/empresas/login')
      return
    }

    const response = await fetch(`${import.meta.env.VITE_API_URL}/api/colaboradores/${colaboradorId}/contrato`, {
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
      throw new Error('Erro ao gerar contrato')
    }

    // Criar link de download
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    
    // Obter nome do arquivo do header se disponível
    const contentDisposition = response.headers.get('content-disposition')
    let filename = 'contrato.docx'
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="?([^"]+)"?/)
      if (filenameMatch) {
        filename = filenameMatch[1]
      }
    }
    
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

  } catch (error) {
    console.error('Erro ao gerar contrato:', error)
    alert('Erro ao gerar contrato. Tente novamente.')
  } finally {
    gerandoContrato.value = false
  }
}

const editarColaborador = (colaborador: any) => {
  // TODO: Implementar modal de edição de dados do colaborador
  console.log('Editar colaborador:', colaborador)
  alert('Funcionalidade de edição será implementada em breve!')
}

const irParaDiasNaoTrabalhados = () => {
  router.push('/empresas/dias-nao-trabalhados')
}

const gerarTodosContratos = async () => {
  if (colaboradores.value.length === 0) return
  
  try {
    gerandoTodosContratos.value = true
    
    // Gerar contratos para todos os colaboradores em paralelo
    const promises = colaboradores.value.map(async (colaborador) => {
      try {
        const token = localStorage.getItem('company_token')
        if (!token) throw new Error('Token não encontrado')

        const response = await fetch(`${import.meta.env.VITE_API_URL}/api/colaboradores/${colaborador.id}/contrato`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        })

        if (!response.ok) throw new Error('Erro ao gerar contrato')

        const blob = await response.blob()
        return {
          colaborador: colaborador.candidatura.nome,
          blob,
          filename: `contrato_${colaborador.candidatura.nome.replace(/\s+/g, '_').toLowerCase()}_${new Date().toISOString().split('T')[0]}.docx`
        }
      } catch (error) {
        console.error(`Erro ao gerar contrato para ${colaborador.candidatura.nome}:`, error)
        return null
      }
    })

    const results = await Promise.all(promises)
    const successfulContracts = results.filter(result => result !== null)

    if (successfulContracts.length === 0) {
      alert('Nenhum contrato pôde ser gerado. Tente novamente.')
      return
    }

    // Fazer download de todos os contratos gerados
    successfulContracts.forEach(contract => {
      if (contract) {
        const url = window.URL.createObjectURL(contract.blob)
        const link = document.createElement('a')
        link.href = url
        link.download = contract.filename
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
      }
    })

    alert(`${successfulContracts.length} contrato(s) foram baixados com sucesso!`)

  } catch (error) {
    console.error('Erro ao gerar contratos:', error)
    alert('Erro ao gerar contratos. Tente novamente.')
  } finally {
    gerandoTodosContratos.value = false
  }
}

const getIdade = (dataNascimento: string) => {
  if (!dataNascimento) return 0
  
  // Parse da data usando apenas a parte da data (ignorando timezone)
  const dataString = dataNascimento.split('T')[0] // "1989-07-10"
  const [ano, mes, dia] = dataString.split('-').map(Number)
  
  const hoje = new Date()
  const anoAtual = hoje.getFullYear()
  const mesAtual = hoje.getMonth() + 1
  const diaAtual = hoje.getDate()
  
  let idade = anoAtual - ano
  
  if (mesAtual < mes || (mesAtual === mes && diaAtual < dia)) {
    idade--
  }
  
  return idade
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchOportunidade(),
    fetchColaboradores()
  ])
})
</script>
