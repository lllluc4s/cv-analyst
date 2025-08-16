<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Meu Perfil</h2>
        
        <!-- Loading state -->
        <div v-if="loading" class="text-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-2 text-sm text-gray-600">Carregando perfil...</p>
        </div>

        <!-- Profile content -->
        <div v-else-if="profile" class="space-y-6">
          <!-- Dados Pessoais -->
          <div class="bg-white border rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dados Pessoais</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm font-medium text-gray-500">Nome Completo</p>
                <p class="text-sm text-gray-900">{{ profile.candidato.nome_completo }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Email</p>
                <p class="text-sm text-gray-900">{{ profile.candidato.email }}</p>
              </div>
            </div>
          </div>

          <!-- Vínculos Profissionais -->
          <div class="bg-white border rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Vínculos Profissionais</h3>
            
            <div v-if="profile.colaboradores.length === 0" class="text-center py-8">
              <div class="text-gray-400 text-5xl mb-4">🏢</div>
              <p class="text-gray-600">Nenhum vínculo profissional encontrado.</p>
            </div>

            <div v-else class="space-y-4">
              <div 
                v-for="colaborador in profile.colaboradores" 
                :key="colaborador.id"
                class="border rounded-lg p-4"
              >
                <div class="flex justify-between items-start mb-4">
                  <div class="flex items-center space-x-3">
                    <div 
                      v-if="colaborador.empresa.logo_url" 
                      class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center"
                    >
                      <img :src="colaborador.empresa.logo_url" :alt="colaborador.empresa.nome" class="w-6 h-6 object-contain">
                    </div>
                    <div 
                      v-else 
                      class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center"
                    >
                      <span class="text-sm font-semibold text-gray-600">
                        {{ colaborador.empresa.nome.charAt(0) }}
                      </span>
                    </div>
                    <div>
                      <h4 class="font-medium text-gray-900">{{ colaborador.posicao }}</h4>
                      <p class="text-sm text-gray-600">{{ colaborador.empresa.nome }}</p>
                    </div>
                  </div>
                  <span 
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                    :class="getStatusClass(colaborador)"
                  >
                    {{ getStatusText(colaborador) }}
                  </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                  <div>
                    <p class="font-medium text-gray-900">Departamento</p>
                    <p class="text-gray-600">{{ colaborador.departamento }}</p>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Data de Início</p>
                    <p class="text-gray-600">{{ formatDate(colaborador.data_inicio_contrato) }}</p>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Data de Fim</p>
                    <p class="text-gray-600">
                      {{ colaborador.data_fim_contrato ? formatDate(colaborador.data_fim_contrato) : 'Sem termo' }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Ações -->
          <div class="bg-white border rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ações</h3>
            <div class="space-y-3">
              <button 
                @click="goToCandidateProfile"
                class="w-full md:w-auto inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Editar perfil de candidato
              </button>
              <button 
                @click="goToCandidateArea"
                class="w-full md:w-auto inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Área do candidato
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import colaboradorService, { type ColaboradorProfile, type ColaboradorData } from '@/services/colaboradorService'

const router = useRouter()
const profile = ref<ColaboradorProfile | null>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    profile.value = await colaboradorService.getProfile()
  } catch (error) {
    console.error('Erro ao carregar perfil:', error)
  } finally {
    loading.value = false
  }
})

const formatDate = (date: string) => {
  return colaboradorService.formatDate(date)
}

const getStatusClass = (colaborador: ColaboradorData) => {
  if (!colaborador.contrato_ativo) {
    return 'bg-red-100 text-red-800'
  }
  
  if (colaborador.sem_termo) {
    return 'bg-green-100 text-green-800'
  }
  
  return 'bg-blue-100 text-blue-800'
}

const getStatusText = (colaborador: ColaboradorData) => {
  if (!colaborador.contrato_ativo) {
    return 'Inativo'
  }
  
  if (colaborador.sem_termo) {
    return 'Ativo - Sem termo'
  }
  
  return 'Ativo - Prazo determinado'
}

const goToCandidateProfile = () => {
  router.push('/candidatos/perfil')
}

const goToCandidateArea = () => {
  router.push('/candidatos/dashboard')
}
</script>
