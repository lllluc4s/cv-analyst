<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Minhas Mensagens</h2>
        <p class="text-gray-600 mt-1">Comunicação com empresas sobre oportunidades</p>
      </div>
    </div>

    <!-- Messages Interface -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div class="flex h-96">
        <!-- Conversations List -->
        <div class="w-1/3 border-r border-gray-200 flex flex-col flex-shrink-0">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Conversas</h3>
          </div>
          <div class="flex-1 overflow-y-auto">
            <div v-if="conversations.length === 0" class="p-4 text-center text-gray-500">
              <p>Nenhuma conversa encontrada</p>
            </div>
            <div
              v-for="conversation in conversations"
              :key="conversation.id"
              @click="selectConversation(conversation)"
              :class="[
                'p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50',
                selectedConversation?.id === conversation.id ? 'bg-blue-50 border-blue-200' : ''
              ]"
            >
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center">
                    <span class="text-sm font-medium text-white">
                      {{ conversation.company?.name?.charAt(0) || 'E' }}
                    </span>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">
                    {{ conversation.company?.name || 'Empresa' }}
                  </p>
                  <p class="text-sm text-gray-500 truncate">
                    {{ conversation.last_message?.content || 'Sem mensagens' }}
                  </p>
                </div>
                <div v-if="conversation.unread_count > 0" class="flex-shrink-0">
                  <span class="bg-blue-600 text-white text-xs rounded-full px-2 py-1">
                    {{ conversation.unread_count }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Messages Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden messages-area">
          <div v-if="selectedConversation" class="flex-1 flex flex-col h-full">
            <!-- Conversation Header -->
            <div class="p-4 border-b border-gray-200 flex-shrink-0">
              <h3 class="text-lg font-semibold text-gray-900">
                {{ selectedConversation.company?.name || 'Empresa' }}
              </h3>
              <p class="text-sm text-gray-500">
                Conversa sobre oportunidades
              </p>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden p-4 space-y-4" ref="messagesContainer">
              <div
                v-for="message in messages"
                :key="message.id"
                :class="[
                  'flex min-w-0 max-w-full',
                  message.sender_type === 'candidato' ? 'justify-end' : 'justify-start'
                ]"
              >
                <div
                  :class="[
                    'max-w-[65%] min-w-0 px-3 py-2 rounded-lg message-container',
                    message.sender_type === 'candidato'
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-200 text-gray-900'
                  ]"
                >
                  <p class="text-sm leading-relaxed message-text">{{ message.content }}</p>
                  <p
                    :class="[
                      'text-xs mt-1',
                      message.sender_type === 'candidato' ? 'text-blue-100' : 'text-gray-500'
                    ]"
                  >
                    {{ formatMessageTime(message.created_at) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Message Input -->
            <div class="p-4 border-t border-gray-200 flex-shrink-0">
              <form @submit.prevent="sendMessage" class="flex space-x-4">
                <input
                  v-model="newMessage"
                  type="text"
                  placeholder="Digite sua mensagem..."
                  class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  :disabled="sending"
                />
                <button
                  type="submit"
                  :disabled="!newMessage.trim() || sending"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
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

          <!-- No Conversation Selected -->
          <div v-else class="flex-1 flex items-center justify-center">
            <div class="text-center">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.874A8.955 8.955 0 013 12a8 8 0 018-8c4.418 0 8 3.582 8 8z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma conversa selecionada</h3>
              <p class="mt-1 text-sm text-gray-500">Selecione uma conversa para ver as mensagens</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import colaboradorService from '@/services/colaboradorService'

interface Company {
  id: number
  name: string
  logo_url?: string
}

interface Message {
  id: number
  content: string
  sender_type: 'company' | 'candidato'
  sender_id: number
  recipient_type: 'company' | 'candidato'
  recipient_id: number
  read_at: string | null
  created_at: string
}

interface Conversation {
  id: number
  company: Company
  last_message: Message | null
  unread_count: number
}

const conversations = ref<Conversation[]>([])
const selectedConversation = ref<Conversation | null>(null)
const messages = ref<Message[]>([])
const newMessage = ref('')
const sending = ref(false)
const messagesContainer = ref<HTMLElement>()

const loadConversations = async () => {
  try {
    const token = localStorage.getItem('candidato_token')
    
    if (!token) {
      console.error('Token de autenticação não encontrado')
      return
    }
    
    // Carregar todas as conversas do candidato
    const response = await fetch('/api/messages/conversations', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      conversations.value = data.conversations || []
    } else {
      console.error('Erro ao carregar conversas:', response.status)
    }
  } catch (error) {
    console.error('Erro ao carregar conversas:', error)
  }
}

const loadMessages = async (companyId: number) => {
  try {
    const token = localStorage.getItem('candidato_token')
    const response = await fetch(`/api/messages?company_id=${companyId}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      messages.value = data.messages || []
      await nextTick()
      scrollToBottom()
    } else {
      console.error('Erro ao carregar mensagens:', response.status, await response.text())
    }
  } catch (error) {
    console.error('Erro ao carregar mensagens:', error)
  }
}

const selectConversation = async (conversation: Conversation) => {
  selectedConversation.value = conversation
  await loadMessages(conversation.company.id)
  
  // Marcar mensagens como lidas
  const unreadMessages = messages.value.filter(m => 
    m.sender_type === 'company' && !m.read_at
  )
  
  if (unreadMessages.length > 0) {
    // As mensagens já são marcadas como lidas automaticamente pelo backend
    conversation.unread_count = 0
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || !selectedConversation.value || sending.value) {
    return
  }

  sending.value = true
  const messageContent = newMessage.value.trim()
  newMessage.value = ''

  try {
    const token = localStorage.getItem('candidato_token')
    const response = await fetch('/api/messages/send', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        company_id: selectedConversation.value.company.id,
        content: messageContent
      })
    })

    if (response.ok) {
      const data = await response.json()
      messages.value.push(data.data)
      await nextTick()
      scrollToBottom()
      
      // Atualizar última mensagem na conversa
      selectedConversation.value.last_message = data.data
    } else {
      console.error('Erro ao enviar mensagem:', response.status, await response.text())
      newMessage.value = messageContent
    }
  } catch (error) {
    console.error('Erro ao enviar mensagem:', error)
    newMessage.value = messageContent
  } finally {
    sending.value = false
  }
}

const formatMessageTime = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 1) return 'Agora'
  if (minutes < 60) return `${minutes}m`
  if (hours < 24) return `${hours}h`
  if (days < 7) return `${days}d`
  
  return date.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: '2-digit'
  })
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

onMounted(async () => {
  await loadConversations()
})
</script>

<style scoped>
/* Container de mensagens - força contenção */
.flex-1.overflow-y-auto {
  contain: layout;
}

/* Força quebra agressiva de texto */
.message-container {
  word-wrap: break-word !important;
  overflow-wrap: anywhere !important;
  word-break: break-word !important;
  hyphens: auto !important;
  max-width: 100% !important;
  box-sizing: border-box !important;
}

.message-text {
  word-wrap: break-word !important;
  overflow-wrap: anywhere !important;
  word-break: break-word !important;
  hyphens: auto !important;
  white-space: pre-wrap !important;
  max-width: 100% !important;
}

/* Garante que nada ultrapasse os limites */
.messages-area {
  overflow: hidden !important;
  max-width: 100% !important;
}
</style>
