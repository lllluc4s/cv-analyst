<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
      <!-- Overlay -->
      <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        @click="$emit('close')"
      ></div>

      <!-- Modal -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <!-- Avatar -->
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-4">
                <span class="text-lg font-bold text-white">
                  {{ candidatura?.nome?.charAt(0)?.toUpperCase() || 'C' }}
                </span>
              </div>
              <div>
                <h3 class="text-lg font-medium text-gray-900">
                  {{ candidatura?.nome || 'Candidato' }}
                </h3>
                <p class="text-sm text-gray-500">{{ candidatura?.email }}</p>
              </div>
            </div>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-600 focus:outline-none"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="bg-white px-6 py-4 max-h-96 overflow-y-auto">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column - Candidate Info -->
            <div class="space-y-4">
              <!-- Basic Info -->
              <div>
                <h4 class="text-sm font-medium text-gray-900 mb-2">Informações Básicas</h4>
                <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Nome:</span>
                    <span class="text-sm font-medium">{{ candidatura?.nome }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Email:</span>
                    <span class="text-sm font-medium">{{ candidatura?.email }}</span>
                  </div>
                  <div v-if="candidatura?.telefone" class="flex justify-between">
                    <span class="text-sm text-gray-600">Telefone:</span>
                    <span class="text-sm font-medium">{{ candidatura?.telefone }}</span>
                  </div>
                  <div v-if="candidatura?.linkedin_url" class="flex justify-between">
                    <span class="text-sm text-gray-600">LinkedIn:</span>
                    <a :href="candidatura.linkedin_url" target="_blank" class="text-sm text-blue-600 hover:underline">
                      Ver Perfil
                    </a>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Data da Candidatura:</span>
                    <span class="text-sm font-medium">{{ formatDate(candidatura?.created_at) }}</span>
                  </div>
                </div>
              </div>

              <!-- Skills -->
              <div v-if="candidatura?.skills_extraidas?.length">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Skills Identificadas</h4>
                <div class="flex flex-wrap gap-2">
                  <span 
                    v-for="skill in candidatura.skills_extraidas" 
                    :key="skill"
                    class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full"
                  >
                    {{ skill }}
                  </span>
                </div>
              </div>

              <!-- Score -->
              <div v-if="candidatura?.pontuacao_skills" class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-900">Score de Compatibilidade:</span>
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                  {{ candidatura.pontuacao_skills }}
                </span>
              </div>

              <!-- Company Rating -->
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-900">Avaliação da Empresa:</span>
                <StarRating 
                  :rating="candidatura?.company_rating"
                  @update="(rating) => updateRating(rating)"
                  showText
                />
              </div>

              <!-- Contratação Section -->
              <div class="border-t pt-4">
                <div v-if="candidatura?.is_contratado" class="bg-green-50 border border-green-200 rounded-lg p-4">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium text-green-800">Candidato Contratado</span>
                  </div>
                  <p class="text-sm text-green-700 mt-1">Este candidato já foi contratado como colaborador.</p>
                </div>
                
                <div v-else class="flex space-x-2">
                  <button
                    @click="showContratarModal = true"
                    class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors text-sm font-medium"
                  >
                    ✅ Contratar Candidato
                  </button>
                </div>
              </div>

              <!-- Notes Section -->
              <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">
                  Notas Privadas
                </label>
                <textarea
                  v-model="localNotes"
                  @blur="saveNotes"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                  placeholder="Adicione suas notas sobre este candidato..."
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">
                  Estas notas são privadas e visíveis apenas para a sua empresa.
                </p>
              </div>
            </div>

            <!-- Right Column - CV Preview -->
            <div>
              <h4 class="text-sm font-medium text-gray-900 mb-2">CV Preview</h4>
              <div class="border border-gray-200 rounded-lg overflow-hidden">
                <iframe
                  v-if="candidatura?.cv_path"
                  :src="filesUrl(`pdf/${candidatura.cv_path.replace('cvs/', '')}`)"
                  class="w-full h-80"
                  frameborder="0"
                ></iframe>
                <div v-else class="flex items-center justify-center h-80 text-gray-500">
                  <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2 text-sm">CV não disponível</p>
                  </div>
                </div>
              </div>
              
              <!-- CV Actions -->
              <div class="mt-3 flex space-x-2">
                <button
                  v-if="candidatura?.cv_path"
                  @click="openCV(filesUrl(`pdf/${candidatura.cv_path.replace('cvs/', '')}`))"
                  class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm"
                >
                  Abrir CV em Nova Aba
                </button>
              </div>
            </div>
          </div>

          <!-- History Section -->
          <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Histórico de Interações</h4>
            <div class="space-y-2">
              <div class="flex items-center text-sm">
                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                <span class="text-gray-600">Candidatura recebida em {{ formatDate(candidatura?.created_at) }}</span>
              </div>
              <div v-if="candidatura?.moved_to_state_at" class="flex items-center text-sm">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                <span class="text-gray-600">Última movimentação em {{ formatDate(candidatura.moved_to_state_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3">
          <button
            @click="$emit('close')"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Fechar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Contratação -->
  <div v-if="showContratarModal" class="fixed inset-0 z-[70] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
      <!-- Overlay -->
      <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        @click="showContratarModal = false"
      ></div>

      <!-- Modal -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full z-[71]">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">
            Contratar {{ candidatura?.nome }}
          </h3>
        </div>

        <!-- Content -->
        <form @submit.prevent="contratarCandidato" class="bg-white px-6 py-4 space-y-4">
          <!-- Posição -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Posição/Cargo
            </label>
            <input
              v-model="contratoForm.posicao"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="Ex: Desenvolvedor Frontend, Designer..."
            >
          </div>

          <!-- Departamento -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Departamento
            </label>
            <input
              v-model="contratoForm.departamento"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="Ex: Tecnologia, Marketing, Vendas..."
            >
          </div>

          <!-- Data de Início (obrigatória) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Data de Início do Contrato *
            </label>
            <input
              v-model="contratoForm.data_inicio_contrato"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            >
          </div>

          <!-- Data de Fim (opcional) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Data de Fim do Contrato (opcional)
            </label>
            <input
              v-model="contratoForm.data_fim_contrato"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            >
            <p class="text-xs text-gray-500 mt-1">Deixe em branco para contrato sem termo</p>
          </div>

          <!-- Vencimento -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Vencimento (€)
            </label>
            <input
              v-model="contratoForm.vencimento"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="Ex: 1000.00"
            >
          </div>

          <!-- Número de Contribuinte -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Número de Contribuinte
            </label>
            <input
              v-model="contratoForm.numero_contribuinte"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="Ex: 123456789"
            >
          </div>

          <!-- Número de Segurança Social -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Número de Segurança Social
            </label>
            <input
              v-model="contratoForm.numero_seguranca_social"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="Ex: 12345678901"
            >
          </div>

          <!-- IBAN -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              IBAN
            </label>
            <input
              v-model="contratoForm.iban"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              placeholder="Ex: PT50000201231234567890154"
            >
          </div>

          <!-- Buttons -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="showContratarModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50"
            >
              {{ loading ? 'Contratando...' : 'Contratar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { filesUrl } from '@/utils/urlBuilder'
import StarRating from '@/components/StarRating.vue'
import axios from 'axios'

interface Props {
  show: boolean
  candidatura: any
}

const props = defineProps<Props>()
const emit = defineEmits(['close', 'updateNotes', 'updateRating'])

const localNotes = ref('')
const loading = ref(false)
const showContratarModal = ref(false)

// Form para contratação
const contratoForm = ref({
  data_inicio_contrato: '',
  data_fim_contrato: '',
  vencimento: '',
  numero_contribuinte: '',
  numero_seguranca_social: '',
  iban: '',
  departamento: '',
  posicao: ''
})

// Update rating function
const updateRating = async (rating: number) => {
  if (!props.candidatura?.id) return
  
  try {
    const token = localStorage.getItem('company_token')
    await axios.patch(`${import.meta.env.VITE_API_URL}/api/candidaturas/${props.candidatura.id}/rating`, {
      rating: rating
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    
    // Emit rating update to parent component
    emit('updateRating', props.candidatura.id, rating)
  } catch (error) {
    console.error('Erro ao atualizar avaliação:', error)
  }
}

// Contratar candidato
const contratarCandidato = async () => {
  if (!props.candidatura?.id) return
  
  loading.value = true
  try {
    const token = localStorage.getItem('company_token')
    await axios.post(`${import.meta.env.VITE_API_URL}/api/candidaturas/${props.candidatura.id}/contratar`, contratoForm.value, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    
    // Marcar como contratado localmente
    if (props.candidatura) {
      props.candidatura.is_contratado = true
    }
    
    showContratarModal.value = false
    alert('Candidato contratado com sucesso!')
    
    // Reset form
    contratoForm.value = {
      data_inicio_contrato: '',
      data_fim_contrato: '',
      vencimento: '',
      numero_contribuinte: '',
      numero_seguranca_social: '',
      iban: '',
      departamento: '',
      posicao: ''
    }
  } catch (error: any) {
    console.error('Erro ao contratar candidato:', error)
    const errorMessage = error.response?.data?.message || 'Erro ao contratar candidato'
    alert(errorMessage)
  } finally {
    loading.value = false
  }
}

// Watch for candidatura changes to load notes
watch(() => props.candidatura, (newCandidatura) => {
  if (newCandidatura?.notas_privadas) {
    localNotes.value = newCandidatura.notas_privadas
  } else {
    localNotes.value = ''
  }
}, { immediate: true })

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('pt-PT', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const openCV = (url: string) => {
  window.open(url, '_blank')
}

const saveNotes = () => {
  if (props.candidatura) {
    emit('updateNotes', props.candidatura.id, localNotes.value)
  }
}
</script>
