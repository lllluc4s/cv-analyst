<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Cabeçalho -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Gestão de Dias Não Trabalhados</h1>
      <p class="mt-2 text-gray-600">Gerencie as solicitações de ausência dos seus colaboradores</p>
    </div>

    <!-- Filtros e Ações -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Filtros -->
        <div class="flex flex-col sm:flex-row gap-4">
          <div>
            <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              id="status-filter"
              v-model="filtros.status"
              @change="aplicarFiltros"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
            >
              <option value="">Todos os status</option>
              <option value="pendente">Pendente</option>
              <option value="aprovado">Aprovado</option>
              <option value="recusado">Recusado</option>
            </select>
          </div>
          
          <div>
            <label for="data-inicio" class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
            <input
              id="data-inicio"
              v-model="filtros.data_inicio"
              @change="aplicarFiltros"
              type="date"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
            />
          </div>
          
          <div>
            <label for="data-fim" class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
            <input
              id="data-fim"
              v-model="filtros.data_fim"
              @change="aplicarFiltros"
              type="date"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
            />
          </div>
        </div>

        <!-- Botões de Exportação -->
        <div class="flex gap-2">
          <button
            @click="exportarPdf"
            :disabled="carregandoExportacao"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span v-if="carregandoExportacao">Gerando...</span>
            <span v-else>Exportar PDF</span>
          </button>
          
          <button
            @click="exportarExcel"
            :disabled="carregandoExportacao"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span v-if="carregandoExportacao">Gerando...</span>
            <span v-else>Exportar Excel</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Estatísticas -->
    <div v-if="estatisticas" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total</dt>
                <dd class="text-lg font-medium text-gray-900">{{ estatisticas.total }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Pendentes</dt>
                <dd class="text-lg font-medium text-gray-900">{{ estatisticas.pendentes }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Aprovadas</dt>
                <dd class="text-lg font-medium text-gray-900">{{ estatisticas.aprovadas }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Recusadas</dt>
                <dd class="text-lg font-medium text-gray-900">{{ estatisticas.recusadas }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de Solicitações -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Solicitações de Ausência</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
          {{ solicitacoes.length }} solicitação(ões) encontrada(s)
        </p>
      </div>

      <div v-if="carregandoLista" class="px-4 py-5 sm:px-6">
        <div class="animate-pulse">
          <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
          <div class="h-4 bg-gray-200 rounded w-1/2"></div>
        </div>
      </div>

      <ul v-else-if="solicitacoes.length > 0" class="divide-y divide-gray-200">
        <li v-for="solicitacao in solicitacoes" :key="solicitacao.id" class="px-4 py-4 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <div class="flex items-center">
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">
                    {{ solicitacao.colaborador?.nome_completo || 'Nome não disponível' }}
                  </p>
                  <p class="text-sm text-gray-500 mt-1">
                    <strong>Data da ausência:</strong> {{ formatDate(solicitacao.data_ausencia) }}
                  </p>
                  <p class="text-sm text-gray-500 mt-1">
                    <strong>Motivo:</strong> {{ solicitacao.motivo }}
                  </p>
                  <div class="mt-2 flex items-center space-x-4">
                    <span :class="getStatusColor(solicitacao.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                      {{ getStatusLabel(solicitacao.status) }}
                    </span>
                    <span class="text-xs text-gray-500">
                      Criado em {{ formatDateTime(solicitacao.created_at) }}
                    </span>
                    <span v-if="solicitacao.documento_path" class="inline-flex items-center text-xs text-blue-600">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      Com documento
                    </span>
                  </div>
                  <div v-if="solicitacao.observacoes_empresa" class="mt-2 p-3 bg-gray-50 rounded-md">
                    <p class="text-sm text-gray-700">
                      <strong>Observações da empresa:</strong> {{ solicitacao.observacoes_empresa }}
                    </p>
                    <p v-if="solicitacao.aprovado_em" class="text-xs text-gray-500 mt-1">
                      Processado em {{ formatDateTime(solicitacao.aprovado_em) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <button
                v-if="solicitacao.documento_path"
                @click="baixarDocumento(solicitacao.id)"
                class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <svg class="-ml-0.5 mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Documento
              </button>
              
              <div v-if="solicitacao.status === 'pendente'" class="flex space-x-1">
                <button
                  @click="abrirModalDecisao(solicitacao, 'aprovar')"
                  class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  <svg class="-ml-0.5 mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Aprovar
                </button>
                
                <button
                  @click="abrirModalDecisao(solicitacao, 'recusar')"
                  class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                  <svg class="-ml-0.5 mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  Recusar
                </button>
              </div>
            </div>
          </div>
        </li>
      </ul>

      <div v-else class="px-4 py-5 sm:px-6 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma solicitação encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">Não há solicitações com os filtros aplicados.</p>
      </div>
    </div>

    <!-- Modal de Decisão -->
    <div
      v-if="modalDecisao.visivel"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click="fecharModalDecisao"
    >
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ modalDecisao.acao === 'aprovar' ? 'Aprovar' : 'Recusar' }} Solicitação
          </h3>
          <div class="mt-2">
            <p class="text-sm text-gray-500">
              {{ modalDecisao.acao === 'aprovar' ? 'Tem certeza que deseja aprovar' : 'Tem certeza que deseja recusar' }}
              a solicitação de ausência de {{ modalDecisao.solicitacao?.colaborador?.nome_completo || 'colaborador' }}?
            </p>
            
            <div class="mt-4">
              <label for="observacoes" class="block text-sm font-medium text-gray-700">
                Observações (opcional)
              </label>
              <textarea
                id="observacoes"
                v-model="modalDecisao.observacoes"
                rows="3"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                placeholder="Adicione observações sobre esta decisão..."
              />
            </div>
          </div>
          
          <div class="flex items-center justify-end space-x-3 mt-6">
            <button
              @click="fecharModalDecisao"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancelar
            </button>
            <button
              @click="confirmarDecisao"
              :disabled="processandoDecisao"
              :class="[
                'px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50',
                modalDecisao.acao === 'aprovar' 
                  ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' 
                  : 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
              ]"
            >
              <span v-if="processandoDecisao">Processando...</span>
              <span v-else>{{ modalDecisao.acao === 'aprovar' ? 'Aprovar' : 'Recusar' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Alertas -->
    <div v-if="mensagem" class="fixed bottom-4 right-4 z-50">
      <div :class="[
        'rounded-md p-4 shadow-lg',
        mensagem.tipo === 'sucesso' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'
      ]">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg v-if="mensagem.tipo === 'sucesso'" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <svg v-else class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p :class="[
              'text-sm font-medium',
              mensagem.tipo === 'sucesso' ? 'text-green-800' : 'text-red-800'
            ]">
              {{ mensagem.texto }}
            </p>
          </div>
          <div class="ml-auto pl-3">
            <button @click="mensagem = null" class="inline-flex text-gray-400 hover:text-gray-500">
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import diasNaoTrabalhadosService, { type DiaNaoTrabalhado } from '@/services/diasNaoTrabalhadosService'
import { companyAuthService, type Company } from '@/services/companyAuth'

const router = useRouter()

// Estado
const solicitacoes = ref<DiaNaoTrabalhado[]>([])
const estatisticas = ref<any>(null)
const carregandoLista = ref(false)
const carregandoExportacao = ref(false)
const processandoDecisao = ref(false)
const mensagem = ref<{ texto: string; tipo: 'sucesso' | 'erro' } | null>(null)

// Filtros
const filtros = ref({
  status: '',
  data_inicio: '',
  data_fim: ''
})

// Modal de decisão
const modalDecisao = ref({
  visivel: false,
  acao: 'aprovar' as 'aprovar' | 'recusar',
  solicitacao: null as DiaNaoTrabalhado | null,
  observacoes: ''
})

// Computed - obter company ID do token
const companyId = ref<number | null>(null)
const currentCompany = ref<Company | null>(null)

// Carregar dados da empresa autenticada
const carregarEmpresa = async () => {
  try {
    const empresa = await companyAuthService.me()
    currentCompany.value = empresa
    companyId.value = empresa.id
  } catch (error) {
    console.error('Erro ao carregar empresa:', error)
    router.push('/empresas/login')
  }
}

// Métodos
const carregarDados = async () => {
  const token = localStorage.getItem('company_token')
  if (!token) {
    router.push('/empresas/login')
    return
  }
  
  // Primeiro carregar dados da empresa
  await carregarEmpresa()
  
  if (!companyId.value) return
  
  carregandoLista.value = true
  try {
    const [solicitacoesResponse, estatisticasResponse] = await Promise.all([
      diasNaoTrabalhadosService.listarSolicitacoesPorEmpresa(
        undefined,
        filtros.value.status || undefined
      ),
      diasNaoTrabalhadosService.obterEstatisticas(companyId.value)
    ])
    
    if (solicitacoesResponse.success) {
      solicitacoes.value = Array.isArray(solicitacoesResponse.data) 
        ? solicitacoesResponse.data 
        : []
    }
    
    if (estatisticasResponse.success) {
      estatisticas.value = estatisticasResponse.data
    }
  } catch (error) {
    console.error('Erro ao carregar dados:', error)
    // Verificar se é erro de autenticação
    if (error instanceof Error && error.message.includes('401')) {
      localStorage.removeItem('company_token')
      router.push('/empresas/login')
      return
    }
    mostrarMensagem('Erro ao carregar dados', 'erro')
  } finally {
    carregandoLista.value = false
  }
}

const aplicarFiltros = async () => {
  await carregarDados()
}

const exportarPdf = async () => {
  if (!companyId.value) return
  
  carregandoExportacao.value = true
  try {
    const blob = await diasNaoTrabalhadosService.exportarPdf(companyId.value, filtros.value)
    
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `dias-nao-trabalhados-${new Date().toISOString().split('T')[0]}.pdf`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
    
    mostrarMensagem('PDF gerado com sucesso!', 'sucesso')
  } catch (error) {
    console.error('Erro ao exportar PDF:', error)
    mostrarMensagem('Erro ao gerar PDF', 'erro')
  } finally {
    carregandoExportacao.value = false
  }
}

const exportarExcel = async () => {
  if (!companyId.value) return
  
  carregandoExportacao.value = true
  try {
    const blob = await diasNaoTrabalhadosService.exportarExcel(companyId.value, filtros.value)
    
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `dias-nao-trabalhados-${new Date().toISOString().split('T')[0]}.xlsx`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
    
    mostrarMensagem('Excel gerado com sucesso!', 'sucesso')
  } catch (error) {
    console.error('Erro ao exportar Excel:', error)
    mostrarMensagem('Erro ao gerar Excel', 'erro')
  } finally {
    carregandoExportacao.value = false
  }
}

const baixarDocumento = async (id: number) => {
  try {
    const blob = await diasNaoTrabalhadosService.baixarDocumento(id)
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `documento_${id}.pdf`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
  } catch (error) {
    console.error('Erro ao baixar documento:', error)
    mostrarMensagem('Erro ao baixar documento', 'erro')
  }
}

const abrirModalDecisao = (solicitacao: DiaNaoTrabalhado, acao: 'aprovar' | 'recusar') => {
  modalDecisao.value = {
    visivel: true,
    acao,
    solicitacao,
    observacoes: ''
  }
}

const fecharModalDecisao = () => {
  modalDecisao.value = {
    visivel: false,
    acao: 'aprovar',
    solicitacao: null,
    observacoes: ''
  }
}

const confirmarDecisao = async () => {
  if (!modalDecisao.value.solicitacao) return
  
  processandoDecisao.value = true
  try {
    const { acao, solicitacao, observacoes } = modalDecisao.value
    
    if (acao === 'aprovar') {
      await diasNaoTrabalhadosService.aprovarSolicitacao(solicitacao.id, observacoes)
      mostrarMensagem('Solicitação aprovada com sucesso!', 'sucesso')
    } else {
      await diasNaoTrabalhadosService.recusarSolicitacao(solicitacao.id, observacoes)
      mostrarMensagem('Solicitação recusada com sucesso!', 'sucesso')
    }
    
    fecharModalDecisao()
    await carregarDados()
  } catch (error) {
    console.error('Erro ao processar decisão:', error)
    mostrarMensagem('Erro ao processar decisão', 'erro')
  } finally {
    processandoDecisao.value = false
  }
}

const mostrarMensagem = (texto: string, tipo: 'sucesso' | 'erro') => {
  mensagem.value = { texto, tipo }
  setTimeout(() => {
    mensagem.value = null
  }, 5000)
}

// Helpers
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const formatDateTime = (dateString: string) => {
  return new Date(dateString).toLocaleString('pt-BR')
}

const getStatusLabel = (status: string) => {
  return diasNaoTrabalhadosService.getStatusLabel(status)
}

const getStatusColor = (status: string) => {
  return diasNaoTrabalhadosService.getStatusColor(status)
}

// Lifecycle
onMounted(() => {
  carregarDados()
})
</script>
