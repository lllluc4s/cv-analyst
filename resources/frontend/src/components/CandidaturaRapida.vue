<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <!-- Para candidatos autenticados -->
    <div v-if="candidatoLogado" class="text-center">
      <div v-if="canApplyData.has_applied" class="mb-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
          ✅ Já se candidatou a esta oportunidade
        </div>
      </div>
      
      <div v-else>
        <button
          @click="candidatar"
          :disabled="loading"
          class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
        >
          <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ loading ? 'Candidatando...' : '🚀 Candidatar-me com 1 Clique' }}
        </button>
        
        <p class="text-sm text-gray-600 mt-2">
          Candidatura automática usando o seu perfil
        </p>
      </div>
    </div>
    
    <!-- Para visitantes não autenticados -->
    <div v-else class="text-center">
      <p class="text-gray-600 mb-4">Entre para se candidatar com 1 clique:</p>
      
      <div class="space-y-3">
        <router-link 
          to="/candidatos/login"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg inline-block"
        >
          Entrar como Candidato
        </router-link>
        
        <div class="text-gray-500">ou</div>
        
        <router-link 
          to="/candidatos/register"
          class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-lg inline-block"
        >
          Criar Conta de Candidato
        </router-link>
      </div>
      
      <div class="mt-6 pt-6 border-t border-gray-200">
        <p class="text-sm text-gray-600 mb-3">
          Ou candidatar-se de forma tradicional:
        </p>
        <slot name="traditional-form" />
      </div>
    </div>
    
    <!-- Mensagens de sucesso/erro -->
    <div v-if="successMessage" class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
      {{ successMessage }}
    </div>
    
    <div v-if="errorMessage" class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { candidatoAuthService } from '../services/candidatoAuth'

interface Props {
  oportunidadeId: number
}

const props = defineProps<Props>()

const candidato = ref(candidatoAuthService.getCurrentCandidato())
const canApplyData = ref({
  can_apply: true,
  has_applied: false
})

const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const candidatoLogado = computed(() => {
  const isAuth = candidatoAuthService.isAuthenticated()
  const currentCandidato = candidato.value
  console.log('CandidaturaRapida: isAuthenticated:', isAuth, 'candidato:', currentCandidato)
  return isAuth && currentCandidato
})

const checkCanApply = async () => {
  if (!candidatoLogado.value) {
    console.log('CandidaturaRapida: Candidato não logado')
    return
  }
  
  console.log('CandidaturaRapida: Verificando se pode candidatar para oportunidade ID:', props.oportunidadeId)
  
  try {
    canApplyData.value = await candidatoAuthService.canApply(props.oportunidadeId)
    console.log('CandidaturaRapida: Resposta canApply:', canApplyData.value)
  } catch (error) {
    console.error('CandidaturaRapida: Erro ao verificar se pode candidatar:', error)
  }
}

const candidatar = async () => {
  console.log('CandidaturaRapida: Iniciando candidatura para oportunidade ID:', props.oportunidadeId)
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''
  
  try {
    const response = await candidatoAuthService.candidatar(props.oportunidadeId)
    console.log('CandidaturaRapida: Resposta candidatura:', response)
    successMessage.value = response.message
    
    // Refresh can apply status
    await checkCanApply()
    
  } catch (error: any) {
    console.error('CandidaturaRapida: Erro ao candidatar:', error)
    errorMessage.value = error.response?.data?.message || 'Erro ao candidatar-se'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (candidatoLogado.value) {
    checkCanApply()
  }
})
</script>
