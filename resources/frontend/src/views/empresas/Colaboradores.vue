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
                      <span class="ml-4 text-sm font-medium text-gray-500">Colaboradores</span>
                    </div>
                  </li>
                </ol>
              </nav>
              <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                Colaboradores
              </h1>
              <p class="mt-1 text-sm text-gray-500">
                Gerir colaboradores contratados através do sistema
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
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
                      Total de Colaboradores
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
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 3v6m6-6v6m4-14h-18m18 0a2 2 0 012 2v14a2 2 0 01-2 2H2a2 2 0 01-2-2V7a2 2 0 012-2"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Contratados Este Mês
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ contratadosEsteMes }}
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
                      Posições Diferentes
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ posicoesUnicas.length }}
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

        <!-- Filters -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                Buscar por nome
              </label>
              <input
                type="text"
                id="search"
                v-model="searchTerm"
                placeholder="Digite o nome do colaborador..."
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>
            <div>
              <label for="posicao-filter" class="block text-sm font-medium text-gray-700 mb-2">
                Filtrar por posição
              </label>
              <select
                id="posicao-filter"
                v-model="posicaoFilter"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="">Todas as posições</option>
                <option v-for="posicao in posicoesUnicas" :key="posicao" :value="posicao">
                  {{ posicao }}
                </option>
              </select>
            </div>
            <div>
              <label for="data-filter" class="block text-sm font-medium text-gray-700 mb-2">
                Ordenar por
              </label>
              <select
                id="data-filter"
                v-model="sortOrder"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="data_contratacao_desc">Data de contratação (mais recente)</option>
                <option value="data_contratacao_asc">Data de contratação (mais antiga)</option>
                <option value="nome_asc">Nome (A-Z)</option>
                <option value="nome_desc">Nome (Z-A)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredColaboradores.length === 0 && !loading" class="text-center py-12">
          <div class="mx-auto h-12 w-12 text-gray-400">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
          </div>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum colaborador encontrado</h3>
          <p class="mt-1 text-sm text-gray-500">
            {{ colaboradores.length === 0 ? 'Ainda não contratou nenhum candidato.' : 'Tente ajustar os filtros para encontrar colaboradores.' }}
          </p>
        </div>

        <!-- Colaboradores Grid -->
        <div v-else>
          <!-- Header da seção -->
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-lg font-medium text-gray-900">Todos os Colaboradores</h3>
              <p class="text-sm text-gray-500">{{ filteredColaboradores.length }} colaborador(es) encontrado(s)</p>
            </div>
            <div>
              <button
                @click="irParaDiasNaoTrabalhados"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Gestão de Dias Não Trabalhados
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="colaborador in filteredColaboradores"
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
                  <div class="flex items-center justify-between text-sm mt-2">
                    <span class="text-gray-500">Oportunidade:</span>
                    <span class="text-xs text-blue-600 truncate">{{ colaborador.candidatura.oportunidade.titulo }}</span>
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

        <!-- Informações da Candidatura Original -->
        <div class="mt-6 pt-6 border-t border-gray-200">
          <h4 class="text-md font-medium text-gray-900 mb-3">Candidatura Original</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-500">Oportunidade</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedColaborador.candidatura.oportunidade.titulo }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500">Data da Candidatura</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(selectedColaborador.candidatura.created_at) }}</p>
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
          
          <button
            @click="gerarContrato(selectedColaborador.id)"
            :disabled="gerandoContrato"
            class="w-full px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50 flex items-center justify-center"
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
        </div>

        <!-- Actions -->
        <div class="mt-6 pt-6 border-t border-gray-200 flex justify-end space-x-3">
          <button
            @click="closeColaboradorModal"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Fechar
          </button>
          <button
            @click="editColaborador"
            class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700"
          >
            Editar Informações
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// State
const loading = ref(true)
const colaboradores = ref<any[]>([])
const selectedColaborador = ref<any>(null)
const searchTerm = ref('')
const posicaoFilter = ref('')
const sortOrder = ref('data_contratacao_desc')
const gerandoContrato = ref(false)

// Computed
const contratadosEsteMes = computed(() => {
  const hoje = new Date()
  const inicioMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1)
  return colaboradores.value.filter(c => new Date(c.data_contratacao) >= inicioMes).length
})

const posicoesUnicas = computed(() => {
  const posicoes = colaboradores.value.map(c => c.posicao).filter(Boolean)
  return [...new Set(posicoes)].sort()
})

const aniversariosHoje = computed(() => {
  const hoje = new Date()
  const diaHoje = hoje.getDate()
  const mesHoje = hoje.getMonth() + 1 // JavaScript months are 0-indexed
  
  return colaboradores.value.filter(c => {
    if (!c.data_nascimento) return false
    
    const dataNascimento = new Date(c.data_nascimento)
    const dia = dataNascimento.getDate()
    const mes = dataNascimento.getMonth() + 1
    
    return dia === diaHoje && mes === mesHoje
  })
})

const filteredColaboradores = computed(() => {
  let filtered = colaboradores.value

  // Filter by search term
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase()
    filtered = filtered.filter(c => 
      c.candidatura.nome.toLowerCase().includes(term)
    )
  }

  // Filter by position
  if (posicaoFilter.value) {
    filtered = filtered.filter(c => c.posicao === posicaoFilter.value)
  }

  // Sort
  filtered.sort((a, b) => {
    switch (sortOrder.value) {
      case 'data_contratacao_desc':
        return new Date(b.data_contratacao).getTime() - new Date(a.data_contratacao).getTime()
      case 'data_contratacao_asc':
        return new Date(a.data_contratacao).getTime() - new Date(b.data_contratacao).getTime()
      case 'nome_asc':
        return a.candidatura.nome.localeCompare(b.candidatura.nome)
      case 'nome_desc':
        return b.candidatura.nome.localeCompare(a.candidatura.nome)
      default:
        return 0
    }
  })

  return filtered
})

// Methods
const fetchColaboradores = async () => {
  try {
    loading.value = true
    const token = localStorage.getItem('company_token')
    
    if (!token) {
      router.push('/empresas/login')
      return
    }

    const response = await fetch(`${import.meta.env.VITE_API_URL}/api/colaboradores`, {
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
      throw new Error('Erro ao carregar colaboradores')
    }

    const data = await response.json()
    colaboradores.value = data.data || []

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

const irParaDiasNaoTrabalhados = () => {
  router.push('/empresas/dias-nao-trabalhados')
}

const editColaborador = () => {
  // TODO: Implement edit functionality
  console.log('Edit colaborador:', selectedColaborador.value)
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

const getIdade = (dataNascimento: string) => {
  if (!dataNascimento) return 0
  
  const nascimento = new Date(dataNascimento)
  const hoje = new Date()
  
  let idade = hoje.getFullYear() - nascimento.getFullYear()
  const mesAtual = hoje.getMonth()
  const mesNascimento = nascimento.getMonth()
  
  if (mesAtual < mesNascimento || (mesAtual === mesNascimento && hoje.getDate() < nascimento.getDate())) {
    idade--
  }
  
  return idade
}

// Lifecycle
onMounted(() => {
  fetchColaboradores()
})
</script>
