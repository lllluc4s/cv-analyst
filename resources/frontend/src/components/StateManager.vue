<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-semibold text-gray-900">Estados da Oportunidade</h3>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center"
      >
        <i class="fas fa-plus mr-2"></i>
        Adicionar Estado
      </button>
    </div>

    <!-- Lista de Estados com Drag & Drop -->
    <div class="space-y-3">
      <div
        v-for="(state, index) in states"
        :key="state.id"
        :draggable="!state.is_default"
        @dragstart="dragStart($event, index)"
        @dragover.prevent
        @drop="drop($event, index)"
        class="flex items-center justify-between p-4 border rounded-lg"
        :class="{
          'bg-gray-50 border-gray-200': state.is_default,
          'bg-white border-gray-300 hover:border-gray-400 cursor-move': !state.is_default
        }"
      >
        <div class="flex items-center space-x-4">
          <!-- Indicador de cor -->
          <div
            class="w-4 h-4 rounded-full border"
            :style="{ backgroundColor: state.cor }"
          ></div>
          
          <!-- Nome do estado -->
          <div>
            <span class="font-medium text-gray-900">{{ state.nome }}</span>
            <span v-if="state.is_default" class="ml-2 text-xs text-gray-500">(Padrão)</span>
          </div>
          
          <!-- Email automation indicator -->
          <div v-if="state.email_enabled" class="text-green-600" title="Email automático ativo">
            <i class="fas fa-envelope-check text-sm"></i>
          </div>
        </div>

        <div class="flex items-center space-x-2" v-if="!state.is_default">
          <button
            @click="editState(state)"
            class="text-blue-600 hover:text-blue-700 p-1"
            title="Editar"
          >
            <i class="fas fa-edit"></i>
          </button>
          <button
            @click="deleteState(state)"
            class="text-red-600 hover:text-red-700 p-1"
            title="Excluir"
          >
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal para Criar/Editar Estado -->
    <div v-if="showCreateModal || editingState" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="p-6">
          <h4 class="text-lg font-semibold text-gray-900 mb-4">
            {{ editingState ? 'Editar Estado' : 'Novo Estado' }}
          </h4>
          
          <form @submit.prevent="saveState" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Estado</label>
              <input
                v-model="stateForm.nome"
                type="text"
                required
                maxlength="100"
                placeholder="Ex: 1ª Entrevista, Proposta Enviada..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cor (opcional)</label>
              <div class="flex items-center space-x-3">
                <input
                  v-model="stateForm.cor"
                  type="color"
                  class="w-12 h-8 border border-gray-300 rounded cursor-pointer"
                >
                <input
                  v-model="stateForm.cor"
                  type="text"
                  pattern="^#[0-9A-Fa-f]{6}$"
                  placeholder="#6B7280"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
              </div>
            </div>
            
            <div>
              <label class="flex items-center">
                <input
                  v-model="stateForm.email_enabled"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <span class="ml-2 text-sm text-gray-700">Enviar email automático ao candidato</span>
              </label>
            </div>
            
            <!-- Configuração de email (se habilitado) -->
            <div v-if="stateForm.email_enabled" class="space-y-3 pl-6 border-l-2 border-blue-200">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assunto do Email</label>
                <input
                  v-model="stateForm.email_subject"
                  type="text"
                  maxlength="255"
                  placeholder="Ex: Candidatura movida para {{ estado }}"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Corpo do Email</label>
                <textarea
                  v-model="stateForm.email_body"
                  maxlength="2000"
                  rows="4"
                  placeholder="Olá &#123;&#123; candidato &#125;&#125;, sua candidatura foi movida para &#123;&#123; estado &#125;&#125;..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">
                  Variáveis disponíveis: &#123;&#123; candidato &#125;&#125;, &#123;&#123; estado &#125;&#125;, &#123;&#123; empresa &#125;&#125;, &#123;&#123; vaga &#125;&#125;
                </p>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                {{ saving ? 'Salvando...' : 'Salvar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Toast de sucesso/erro -->
    <div
      v-if="message"
      class="fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50"
      :class="messageType === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'"
    >
      {{ message }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { companyAuthService } from '../services/companyAuth'

interface BoardState {
  id: number
  nome: string
  cor: string
  ordem: number
  is_default: boolean
  email_enabled: boolean
  email_subject?: string
  email_body?: string
}

const props = defineProps<{
  oportunidadeId: number
}>()

const states = ref<BoardState[]>([])
const showCreateModal = ref(false)
const editingState = ref<BoardState | null>(null)
const saving = ref(false)
const message = ref('')
const messageType = ref<'success' | 'error'>('success')
const draggedIndex = ref<number | null>(null)

const stateForm = ref({
  nome: '',
  cor: '#6B7280',
  email_enabled: false,
  email_subject: '',
  email_body: ''
})

const loadStates = async () => {
  try {
    const response = await companyAuthService.api.get(`/companies/kanban/oportunidades/${props.oportunidadeId}/states`)
    states.value = response.data
  } catch (error) {
    console.error('Erro ao carregar estados:', error)
    showMessage('Erro ao carregar estados', 'error')
  }
}

const saveState = async () => {
  saving.value = true
  try {
    if (editingState.value) {
      // Atualizar estado existente
      await companyAuthService.api.put(
        `/companies/kanban/oportunidades/${props.oportunidadeId}/states/${editingState.value.id}`,
        stateForm.value
      )
      showMessage('Estado atualizado com sucesso!', 'success')
    } else {
      // Criar novo estado
      await companyAuthService.api.post(
        `/companies/kanban/oportunidades/${props.oportunidadeId}/states`,
        stateForm.value
      )
      showMessage('Estado criado com sucesso!', 'success')
    }
    
    await loadStates()
    closeModal()
  } catch (error: any) {
    console.error('Erro ao salvar estado:', error)
    showMessage(error.response?.data?.message || 'Erro ao salvar estado', 'error')
  } finally {
    saving.value = false
  }
}

const editState = (state: BoardState) => {
  editingState.value = state
  stateForm.value = {
    nome: state.nome,
    cor: state.cor,
    email_enabled: state.email_enabled,
    email_subject: state.email_subject || '',
    email_body: state.email_body || ''
  }
}

const deleteState = async (state: BoardState) => {
  if (!confirm(`Tem certeza que deseja excluir o estado "${state.nome}"?`)) {
    return
  }
  
  try {
    await companyAuthService.api.delete(
      `/companies/kanban/oportunidades/${props.oportunidadeId}/states/${state.id}`
    )
    showMessage('Estado excluído com sucesso!', 'success')
    await loadStates()
  } catch (error: any) {
    console.error('Erro ao excluir estado:', error)
    showMessage(error.response?.data?.message || 'Erro ao excluir estado', 'error')
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingState.value = null
  stateForm.value = {
    nome: '',
    cor: '#6B7280',
    email_enabled: false,
    email_subject: '',
    email_body: ''
  }
}

// Drag & Drop
const dragStart = (event: DragEvent, index: number) => {
  draggedIndex.value = index
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move'
  }
}

const drop = async (event: DragEvent, dropIndex: number) => {
  event.preventDefault()
  
  if (draggedIndex.value === null || draggedIndex.value === dropIndex) {
    return
  }
  
  // Reordenar localmente
  const draggedState = states.value[draggedIndex.value]
  states.value.splice(draggedIndex.value, 1)
  states.value.splice(dropIndex, 0, draggedState)
  
  // Atualizar ordem no servidor
  const statesWithOrder = states.value
    .filter(s => !s.is_default)
    .map((state, index) => ({
      id: state.id,
      ordem: index
    }))
  
  try {
    await companyAuthService.api.post(
      `/companies/kanban/oportunidades/${props.oportunidadeId}/states/reorder`,
      { states: statesWithOrder }
    )
    showMessage('Ordem dos estados atualizada!', 'success')
  } catch (error) {
    console.error('Erro ao reordenar estados:', error)
    showMessage('Erro ao reordenar estados', 'error')
    // Recarregar para reverter mudanças
    await loadStates()
  }
  
  draggedIndex.value = null
}

const showMessage = (text: string, type: 'success' | 'error') => {
  message.value = text
  messageType.value = type
  setTimeout(() => {
    message.value = ''
  }, 4000)
}

onMounted(() => {
  loadStates()
})
</script>
