<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Candidatos com Potencial</h2>
        <p class="mt-1 text-sm text-gray-600">
          {{ oportunidade?.titulo }}
        </p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="activeTab = 'candidatos'"
          :class="[
            'px-4 py-2 text-sm font-medium rounded-md',
            activeTab === 'candidatos'
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
          ]"
        >
          Candidatos ({{ candidatos.length }})
        </button>
        <button
          @click="activeTab = 'historico'; loadHistorico()"
          :class="[
            'px-4 py-2 text-sm font-medium rounded-md',
            activeTab === 'historico'
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
          ]"
        >
          Histórico de Convites
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin mx-auto h-8 w-8 text-blue-600">
        <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
      <p class="mt-2 text-gray-500">A carregar candidatos...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <div class="text-red-600 mb-2">
        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <p class="text-gray-900 font-medium">Erro ao carregar dados</p>
      <p class="text-gray-500 mt-1">{{ error }}</p>
      <button 
        @click="loadCandidatos"
        class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm"
      >
        Tentar novamente
      </button>
    </div>

    <!-- Content -->
    <div v-else>
      <!-- Tab: Candidatos -->
      <div v-if="activeTab === 'candidatos'">
        <!-- Empty State -->
        <div v-if="candidatos.length === 0" class="text-center py-12">
          <div class="text-gray-400 mb-4">
            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum candidato encontrado</h3>
          <p class="text-gray-500">
            Não foram encontrados candidatos com skills relevantes para esta oportunidade.
          </p>
        </div>

        <!-- Candidates List -->
        <div v-else class="bg-white shadow overflow-hidden sm:rounded-md">
          <ul class="divide-y divide-gray-200">
            <li v-for="candidato in candidatos" :key="candidato.id" class="px-6 py-6">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <!-- Candidate Info -->
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                        <span class="text-sm font-medium text-white">
                          {{ candidato.nome.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4 flex-1">
                      <div class="flex items-center">
                        <h3 class="text-lg font-medium text-gray-900">
                          {{ candidato.nome }}
                        </h3>
                        <div class="ml-3">
                          <span 
                            :class="[
                              'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                              getAffinityColorClass(candidato.afinidade_percentual)
                            ]"
                          >
                            {{ candidato.afinidade_percentual }}% de afinidade
                          </span>
                        </div>
                      </div>
                      <p class="text-sm text-gray-600">{{ candidato.email }}</p>
                    </div>
                  </div>

                  <!-- Skills Principais -->
                  <div v-if="candidato.skills_principais.length > 0" class="mt-3">
                    <p class="text-sm font-medium text-gray-900 mb-2">Skills em comum:</p>
                    <div class="flex flex-wrap gap-2">
                      <span 
                        v-for="skill in candidato.skills_principais" 
                        :key="skill.nome"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                      >
                        {{ skill.nome }}
                        <span class="ml-1 text-green-600">({{ skill.peso }}%)</span>
                      </span>
                    </div>
                  </div>

                  <!-- Actions Links -->
                  <div class="mt-3 flex space-x-4">
                    <button 
                      v-if="candidato.cv_path"
                      @click="openPreviewCv(candidato.cv_path)"
                      class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      Ver CV
                    </button>
                    <a 
                      v-if="candidato.linkedin_url"
                      :href="candidato.linkedin_url" 
                      target="_blank"
                      class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
                    >
                      <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                      </svg>
                      LinkedIn
                    </a>
                  </div>
                </div>

                <!-- Invite Button -->
                <div class="ml-6">
                  <button
                    @click="openInviteModal(candidato)"
                    :disabled="enviandoConvite === candidato.id"
                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center"
                  >
                    <svg v-if="enviandoConvite === candidato.id" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ enviandoConvite === candidato.id ? 'Enviando...' : 'Convidar para Oportunidade' }}
                  </button>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Tab: Histórico -->
      <div v-else-if="activeTab === 'historico'">
        <div v-if="convites.length === 0" class="text-center py-12">
          <div class="text-gray-400 mb-4">
            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum convite enviado</h3>
          <p class="text-gray-500">
            Ainda não foram enviados convites para esta oportunidade.
          </p>
        </div>

        <div v-else class="bg-white shadow overflow-hidden sm:rounded-md">
          <ul class="divide-y divide-gray-200">
            <li v-for="convite in convites" :key="convite.id" class="px-6 py-4">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-sm font-medium text-gray-900">{{ convite.candidato.nome }}</h3>
                  <p class="text-sm text-gray-500">{{ convite.candidato.email }}</p>
                  <p class="text-xs text-gray-400 mt-1">
                    Enviado em {{ formatDate(convite.enviado_em) }}
                  </p>
                </div>
                <div class="flex items-center space-x-3">
                  <span 
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      convite.candidatou_se 
                        ? 'bg-green-100 text-green-800' 
                        : convite.visualizado_em 
                          ? 'bg-yellow-100 text-yellow-800'
                          : 'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ 
                      convite.candidatou_se 
                        ? '✅ Candidatou-se' 
                        : convite.visualizado_em 
                          ? '👀 Visualizado'
                          : '📧 Enviado'
                    }}
                  </span>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Modal: Invite Candidate -->
    <div v-if="showInviteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 text-center mb-4">
            Convidar {{ selectedCandidato?.nome }}
          </h3>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Mensagem personalizada (opcional):
            </label>
            <textarea
              v-model="mensagemPersonalizada"
              rows="4"
              maxlength="1000"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="Adicione uma mensagem personalizada para o candidato..."
            ></textarea>
            <p class="text-xs text-gray-500 mt-1">{{ mensagemPersonalizada.length }}/1000 caracteres</p>
          </div>
          
          <div class="flex gap-3">
            <button
              @click="closeInviteModal"
              class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium"
            >
              Cancelar
            </button>
            <button
              @click="sendInvite"
              :disabled="enviandoConvite !== false"
              class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm font-medium"
            >
              {{ enviandoConvite !== false ? 'Enviando...' : 'Enviar Convite' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Preview do CV -->
  <div v-if="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full max-h-[90vh] flex flex-col">
      <div class="flex justify-between items-center p-4 border-b">
        <h3 class="text-lg font-semibold">Preview do CV</h3>
        <button @click="closePreviewCv" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
      </div>
      <div class="flex-1 overflow-auto">
        <iframe
          v-if="previewCvUrl"
          :src="previewCvUrl"
          class="w-full h-[75vh] min-h-[600px]"
          frameborder="0"
        ></iframe>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { candidatosPotencialService, type CandidatoPotencial, type ConviteHistorico } from '@/services/candidatosPotencial'
import { UrlBuilder } from '@/utils/urlBuilder'

const route = useRoute()
const oportunidadeId = Number(route.params.id)

// State
const loading = ref(false)
const error = ref('')
const activeTab = ref('candidatos')
const candidatos = ref<CandidatoPotencial[]>([])
const convites = ref<ConviteHistorico[]>([])
const oportunidade = ref<any>(null)

// Modal state
const showInviteModal = ref(false)
const selectedCandidato = ref<CandidatoPotencial | null>(null)
const mensagemPersonalizada = ref('')
const enviandoConvite = ref<number | false>(false)

// Preview CV state
const showPreviewModal = ref(false)
const previewCvUrl = ref<string | null>(null)
const BACKEND_URL = UrlBuilder.getApiBaseUrl()

// Methods
const loadCandidatos = async () => {
  try {
    loading.value = true
    error.value = ''
    
    const response = await candidatosPotencialService.buscarCandidatosPotencial(oportunidadeId)
    candidatos.value = response.candidatos
    oportunidade.value = response.oportunidade
    
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro ao carregar candidatos com potencial'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const loadHistorico = async () => {
  try {
    const response = await candidatosPotencialService.historicoConvites(oportunidadeId)
    convites.value = response.convites
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro ao carregar histórico de convites'
    console.error(err)
  }
}

// Preview CV methods
const openPreviewCv = (cvPath: string) => {
  try {
    previewCvUrl.value = `${BACKEND_URL}/storage/${cvPath}`
    showPreviewModal.value = true
  } catch (e) {
    alert('Não foi possível carregar o PDF para preview.')
  }
}

const closePreviewCv = () => {
  showPreviewModal.value = false
  previewCvUrl.value = null
}

const openInviteModal = (candidato: CandidatoPotencial) => {
  selectedCandidato.value = candidato
  mensagemPersonalizada.value = ''
  showInviteModal.value = true
}

const closeInviteModal = () => {
  showInviteModal.value = false
  selectedCandidato.value = null
  mensagemPersonalizada.value = ''
}

const sendInvite = async () => {
  if (!selectedCandidato.value) return
  
  try {
    enviandoConvite.value = selectedCandidato.value.id
    
    await candidatosPotencialService.convidarCandidato({
      candidato_id: selectedCandidato.value.id,
      oportunidade_id: oportunidadeId,
      mensagem_personalizada: mensagemPersonalizada.value || undefined
    })
    
    // Remove candidato da lista (já foi convidado)
    candidatos.value = candidatos.value.filter(c => c.id !== selectedCandidato.value!.id)
    
    closeInviteModal()
    
    // Mostrar mensagem de sucesso
    alert('Convite enviado com sucesso!')
    
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro ao enviar convite'
    console.error(err)
  } finally {
    enviandoConvite.value = false
  }
}

const getAffinityColorClass = (percentage: number) => {
  if (percentage >= 80) return 'bg-green-100 text-green-800'
  if (percentage >= 60) return 'bg-blue-100 text-blue-800'
  if (percentage >= 40) return 'bg-yellow-100 text-yellow-800'
  return 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-PT', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  loadCandidatos()
})
</script>
