<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Cabeçalho -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Dias Não Trabalhados</h1>
      <p class="mt-2 text-gray-600">Gerencie suas solicitações de ausência</p>
    </div>

    <!-- Botão Nova Solicitação -->
    <div class="mb-6">
      <button
        @click="mostrarFormulario = true"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Nova Solicitação
      </button>
    </div>

    <!-- Formulário de Nova Solicitação -->
    <div v-if="mostrarFormulario" class="mb-8 bg-white shadow rounded-lg p-6">
      <h2 class="text-lg font-medium text-gray-900 mb-4">Nova Solicitação de Ausência</h2>
      
      <form @submit.prevent="criarSolicitacao" class="space-y-4">
        <div>
          <label for="data_ausencia" class="block text-sm font-medium text-gray-700">Data da Ausência</label>
          <input
            type="date"
            id="data_ausencia"
            v-model="formulario.data_ausencia"
            :min="new Date().toISOString().split('T')[0]"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            required
          />
        </div>

        <div>
          <label for="motivo" class="block text-sm font-medium text-gray-700">Motivo da Ausência</label>
          <textarea
            id="motivo"
            v-model="formulario.motivo"
            rows="3"
            maxlength="500"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="Descreva o motivo da sua ausência..."
            required
          />
          <p class="mt-1 text-sm text-gray-500">{{ formulario.motivo.length }}/500 caracteres</p>
        </div>

        <div>
          <label for="documento" class="block text-sm font-medium text-gray-700">Documento Justificativo (Opcional)</label>
          <input
            type="file"
            id="documento"
            @change="handleFileUpload"
            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
          />
          <p class="mt-1 text-sm text-gray-500">Formatos aceitos: PDF, JPG, PNG, DOC, DOCX (máx. 5MB)</p>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="cancelarFormulario"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="carregando"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="carregando">Enviando...</span>
            <span v-else>Enviar Solicitação</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Lista de Solicitações -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Suas Solicitações</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Histórico de solicitações de ausência</p>
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
                    {{ formatDate(solicitacao.data_ausencia) }}
                  </p>
                  <p class="text-sm text-gray-500 mt-1">
                    {{ solicitacao.motivo }}
                  </p>
                  <div class="mt-2 flex items-center space-x-4">
                    <span :class="getStatusColor(solicitacao.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                      {{ getStatusLabel(solicitacao.status) }}
                    </span>
                    <span class="text-xs text-gray-500">
                      Criado em {{ formatDateTime(solicitacao.created_at) }}
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
            </div>
          </div>
        </li>
      </ul>

      <div v-else class="px-4 py-5 sm:px-6 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma solicitação</h3>
        <p class="mt-1 text-sm text-gray-500">Ainda não há solicitações de ausência.</p>
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
import colaboradorService, { type ColaboradorProfile } from '@/services/colaboradorService'
import diasNaoTrabalhadosService, { type DiaNaoTrabalhado } from '@/services/diasNaoTrabalhadosService'

// Estado
const solicitacoes = ref<DiaNaoTrabalhado[]>([])
const carregandoLista = ref(false)
const carregando = ref(false)
const mostrarFormulario = ref(false)
const mensagem = ref<{ texto: string; tipo: 'sucesso' | 'erro' } | null>(null)
const profile = ref<ColaboradorProfile | null>(null)

// Formulário
const formulario = ref({
  data_ausencia: '',
  motivo: '',
  documento: null as File | null
})

// Colaborador atual
const colaborador = computed(() => {
  // Buscar dados do colaborador no profile
  if (profile.value?.colaboradores && profile.value.colaboradores.length > 0) {
    // Retornar o primeiro colaborador ativo (ou o primeiro se não houver ativo)
    const colaboradorAtivo = profile.value.colaboradores.find(c => c.contrato_ativo)
    return colaboradorAtivo || profile.value.colaboradores[0]
  }
  return null
})

// Métodos
const carregarDados = async () => {
  try {
    // Carregar perfil do colaborador
    profile.value = await colaboradorService.getProfile()
    
    // Carregar solicitações
    await carregarSolicitacoes()
  } catch (error) {
    console.error('Erro ao carregar dados:', error)
    mostrarMensagem('Erro ao carregar dados do colaborador', 'erro')
  }
}

const carregarSolicitacoes = async () => {
  if (!colaborador.value) return
  
  carregandoLista.value = true
  try {
    const response = await diasNaoTrabalhadosService.listarSolicitacoes(colaborador.value.id)
    if (response.success) {
      solicitacoes.value = Array.isArray(response.data) ? response.data : []
    }
  } catch (error) {
    console.error('Erro ao carregar solicitações:', error)
    mostrarMensagem('Erro ao carregar solicitações', 'erro')
  } finally {
    carregandoLista.value = false
  }
}

const criarSolicitacao = async () => {
  if (!colaborador.value) return
  
  carregando.value = true
  try {
    const response = await diasNaoTrabalhadosService.criarSolicitacao({
      colaborador_id: colaborador.value.id,
      data_ausencia: formulario.value.data_ausencia,
      motivo: formulario.value.motivo,
      documento: formulario.value.documento || undefined
    })
    
    if (response.success) {
      mostrarMensagem('Solicitação criada com sucesso!', 'sucesso')
      cancelarFormulario()
      carregarSolicitacoes()
    }
  } catch (error: any) {
    console.error('Erro ao criar solicitação:', error)
    const mensagemErro = error.response?.data?.message || 'Erro ao criar solicitação'
    mostrarMensagem(mensagemErro, 'erro')
  } finally {
    carregando.value = false
  }
}

const cancelarFormulario = () => {
  mostrarFormulario.value = false
  formulario.value = {
    data_ausencia: '',
    motivo: '',
    documento: null
  }
}

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    if (file.size > 5 * 1024 * 1024) { // 5MB
      mostrarMensagem('Arquivo muito grande. Máximo 5MB.', 'erro')
      target.value = ''
      return
    }
    formulario.value.documento = file
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
