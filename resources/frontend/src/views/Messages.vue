<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="flex h-96">
        <!-- Lista de Conversas -->
        <div class="w-1/3 border-r border-gray-200">
          <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Mensagens</h2>
            <button 
              v-if="userType === 'candidato'"
              @click="showCompanySelect = true"
              class="mt-2 w-full bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700"
            >
              Nova Conversa
            </button>
            <button 
              v-else
              @click="showCandidatoSelect = true"
              class="mt-2 w-full bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700"
            >
              Nova Conversa
            </button>
          </div>
          
          <!-- Lista de conversas -->
          <div class="overflow-y-auto h-80">
            <div 
              v-for="conversation in conversations" 
              :key="userType === 'company' ? conversation.candidato.id : conversation.company.id"
              @click="selectConversation(conversation)"
              class="p-4 hover:bg-gray-50 cursor-pointer border-b border-gray-100"
              :class="{ 'bg-blue-50': selectedConversation && (userType === 'company' ? selectedConversation.candidato.id === conversation.candidato.id : selectedConversation.company.id === conversation.company.id) }"
            >
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <img 
                    class="h-10 w-10 rounded-full object-cover" 
                    :src="userType === 'company' ? (conversation.candidato.foto_url || '/images/default-avatar.png') : (conversation.company.logo_url || '/images/default-company.png')" 
                    :alt="userType === 'company' ? conversation.candidato.nome : conversation.company.name"
                  />
                </div>
                <div class="ml-4 flex-1">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">
                      {{ userType === 'company' ? conversation.candidato.nome : conversation.company.name }}
                    </p>
                    <span v-if="conversation.unread_count > 0" class="bg-red-500 text-white text-xs rounded-full px-2 py-1">
                      {{ conversation.unread_count }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-500">
                    {{ formatDate(conversation.last_message_at) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Área de Mensagens -->
        <div class="flex-1 flex flex-col">
          <div v-if="!selectedConversation" class="flex-1 flex items-center justify-center">
            <div class="text-center">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma conversa selecionada</h3>
              <p class="mt-1 text-sm text-gray-500">Selecione uma conversa para começar a enviar mensagens.</p>
            </div>
          </div>

          <div v-else class="flex-1 flex flex-col">
            <!-- Header da conversa -->
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
              <div class="flex items-center">
                <img 
                  class="h-8 w-8 rounded-full object-cover" 
                  :src="userType === 'company' ? (selectedConversation.candidato.foto_url || '/images/default-avatar.png') : (selectedConversation.company.logo_url || '/images/default-company.png')"
                  :alt="userType === 'company' ? selectedConversation.candidato.nome : selectedConversation.company.name"
                />
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900">
                    {{ userType === 'company' ? selectedConversation.candidato.nome : selectedConversation.company.name }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Mensagens -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="messagesContainer">
              <div 
                v-for="message in messages" 
                :key="message.id"
                class="flex"
                :class="{ 'justify-end': isOwnMessage(message), 'justify-start': !isOwnMessage(message) }"
              >
                <div 
                  class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg text-sm"
                  :class="isOwnMessage(message) ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900'"
                >
                  <p>{{ message.content }}</p>
                  <p class="text-xs mt-1 opacity-75">
                    {{ formatTime(message.created_at) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Input de nova mensagem -->
            <div class="px-4 py-3 border-t border-gray-200">
              <form @submit.prevent="sendMessage" class="flex space-x-2">
                <textarea
                  v-model="newMessage"
                  @keydown.enter.prevent="sendMessage"
                  rows="1"
                  class="flex-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm resize-none"
                  placeholder="Digite sua mensagem..."
                  :disabled="sending"
                ></textarea>
                <button
                  type="submit"
                  :disabled="!newMessage.trim() || sending"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="sending" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Enviar
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para selecionar empresa -->
    <div v-if="showCompanySelect" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 text-center mb-4">Selecionar Empresa</h3>
          <div class="max-h-64 overflow-y-auto">
            <div 
              v-for="company in companies" 
              :key="company.id"
              @click="startConversationWithCompany(company)"
              class="flex items-center p-3 hover:bg-gray-50 cursor-pointer rounded-md"
            >
              <img 
                class="h-8 w-8 rounded-full object-cover" 
                :src="company.logo_url || '/images/default-company.png'"
                :alt="company.name"
              />
              <span class="ml-3 text-sm font-medium text-gray-900">{{ company.name }}</span>
            </div>
          </div>
          <div class="flex justify-end mt-4 space-x-2">
            <button @click="showCompanySelect = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para selecionar candidato -->
    <div v-if="showCandidatoSelect" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 text-center mb-4">Selecionar Candidato</h3>
          <div class="max-h-64 overflow-y-auto">
            <div 
              v-for="candidato in candidatos" 
              :key="candidato.id"
              @click="startConversationWithCandidato(candidato)"
              class="flex items-center p-3 hover:bg-gray-50 cursor-pointer rounded-md"
            >
              <img 
                class="h-8 w-8 rounded-full object-cover" 
                :src="candidato.foto_url || '/images/default-avatar.png'"
                :alt="candidato.nome"
              />
              <span class="ml-3 text-sm font-medium text-gray-900">{{ candidato.nome }}</span>
            </div>
          </div>
          <div class="flex justify-end mt-4 space-x-2">
            <button @click="showCandidatoSelect = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import echo from '@/services/echo'
import { companyAuthService } from '@/services/companyAuth'
import { candidatoAuthService } from '@/services/candidatoAuth'

const authStore = useAuthStore()

const conversations = ref([])
const selectedConversation = ref(null)
const messages = ref([])
const newMessage = ref('')
const sending = ref(false)
const companies = ref([])
const candidatos = ref([])
const showCompanySelect = ref(false)
const showCandidatoSelect = ref(false)
const messagesContainer = ref(null)
let echoChannel = null

// Determinar tipo de usuário
const userType = computed(() => {
  if (companyAuthService.isAuthenticated()) return 'company'
  if (candidatoAuthService.isAuthenticated()) return 'candidato'
  return null
})

const currentUserId = computed(() => {
  if (userType.value === 'company') {
    const company = companyAuthService.getCurrentCompany()
    return company?.id
  } else if (userType.value === 'candidato') {
    const candidato = candidatoAuthService.getCurrentCandidato()
    return candidato?.id
  }
  return null
})

const loadConversations = async () => {
  try {
    const response = await api.get('/messages/conversations')
    conversations.value = response.data.conversations
  } catch (error) {
    console.error('Erro ao carregar conversas:', error)
  }
}

const loadMessages = async () => {
  if (!selectedConversation.value) return

  try {
    const params = {}
    if (userType.value === 'company') {
      params.candidato_id = selectedConversation.value.candidato.id
    } else {
      params.company_id = selectedConversation.value.company.id
    }

    const response = await api.get('/messages', { params })
    messages.value = response.data.messages
    
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Erro ao carregar mensagens:', error)
  }
}

const setupEchoChannel = () => {
  if (!selectedConversation.value) return

  // Disconnect previous channel if exists
  if (echoChannel) {
    echo.leave(echoChannel.name)
  }

  const companyId = userType.value === 'company' ? 
    currentUserId.value : 
    selectedConversation.value.company.id
  
  const candidatoId = userType.value === 'candidato' ? 
    currentUserId.value : 
    selectedConversation.value.candidato.id

  const channelName = `conversation.${companyId}.${candidatoId}`
  
  echoChannel = echo.private(channelName)
    .listen('.message.new', (e) => {
      console.log('Nova mensagem recebida:', e)
      
      // Adicionar mensagem à lista se não for do usuário atual
      if (!(e.sender_type === userType.value && e.sender_id === currentUserId.value)) {
        messages.value.push({
          id: e.id,
          content: e.content,
          sender_type: e.sender_type,
          sender_id: e.sender_id,
          sender_name: e.sender,
          created_at: e.created_at,
          read_at: null
        })
        
        nextTick(() => {
          scrollToBottom()
        })
        
        // Atualizar conversas para mostrar nova mensagem
        loadConversations()
      }
    })
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value || !selectedConversation.value) return

  sending.value = true
  
  try {
    const data = {
      content: newMessage.value.trim()
    }

    if (userType.value === 'company') {
      data.candidato_id = selectedConversation.value.candidato.id
    } else {
      data.company_id = selectedConversation.value.company.id
    }

    const response = await api.post('/messages/send', data)
    
    // Adicionar mensagem à lista local
    messages.value.push({
      id: response.data.data.id,
      content: response.data.data.content,
      sender_type: response.data.data.sender_type,
      sender_id: response.data.data.sender_id,
      sender_name: userType.value === 'company' ? 
        companyAuthService.getCurrentCompany()?.name : 
        candidatoAuthService.getCurrentCandidato()?.nome + ' ' + candidatoAuthService.getCurrentCandidato()?.apelido,
      created_at: response.data.data.created_at,
      read_at: null
    })

    newMessage.value = ''
    await nextTick()
    scrollToBottom()
    
    // Recarregar conversas para atualizar a última mensagem
    loadConversations()
    
  } catch (error) {
    console.error('Erro ao enviar mensagem:', error)
  } finally {
    sending.value = false
  }
}

const selectConversation = (conversation) => {
  selectedConversation.value = conversation
  loadMessages()
  setupEchoChannel()
}

const isOwnMessage = (message) => {
  return message.sender_type === userType.value && message.sender_id === currentUserId.value
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('pt-PT', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString('pt-PT', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const loadCompanies = async () => {
  try {
    const response = await api.get('/messages/companies')
    companies.value = response.data.companies
  } catch (error) {
    console.error('Erro ao carregar empresas:', error)
  }
}

const loadCandidatos = async () => {
  try {
    const response = await api.get('/messages/candidatos')
    candidatos.value = response.data.candidatos
  } catch (error) {
    console.error('Erro ao carregar candidatos:', error)
  }
}

const startConversationWithCompany = (company) => {
  selectedConversation.value = {
    company: {
      id: company.id,
      name: company.name,
      logo_url: company.logo_url
    }
  }
  messages.value = []
  showCompanySelect.value = false
  setupEchoChannel()
}

const startConversationWithCandidato = (candidato) => {
  selectedConversation.value = {
    candidato: {
      id: candidato.id,
      nome: candidato.nome,
      foto_url: candidato.foto_url
    }
  }
  messages.value = []
  showCandidatoSelect.value = false
  setupEchoChannel()
}

onMounted(() => {
  loadConversations()
  
  if (userType.value === 'candidato') {
    loadCompanies()
  } else if (userType.value === 'company') {
    loadCandidatos()
  }
})

onUnmounted(() => {
  // Cleanup Echo channel
  if (echoChannel) {
    echo.leave(echoChannel.name)
  }
})
</script>
