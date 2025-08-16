<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { oportunidadesService, type Oportunidade } from '../../services/api'
import axios from 'axios'
import CandidaturaForm from '../../components/CandidaturaForm.vue'
import CandidaturaRapida from '../../components/CandidaturaRapida.vue'
import RecaptchaDisclaimer from '../../components/RecaptchaDisclaimer.vue'
import OAuthDebug from '../../components/auth/OAuthDebug.vue'
import ShareButtons from '../../components/ShareButtons.vue'
import { useRecaptcha } from '../../services/recaptcha'
import { UrlBuilder } from '@/utils/urlBuilder'

const route = useRoute()
const oportunidade = ref<Oportunidade | null>(null)
const loading = ref(true)
const error = ref('')
const { getToken } = useRecaptcha()
const recaptchaToken = ref('')

// URL atual para partilha
const currentUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return window.location.href
  }
  return `http://localhost:5174/oportunidade/${route.params.slug}`
})

// Carregar dados da oportunidade
const loadOportunidade = async () => {
  try {
    const slug = route.params.slug as string
    const response = await oportunidadesService.getBySlug(slug)
    oportunidade.value = response
  } catch (err) {
    console.error('Erro ao carregar oportunidade:', err)
    error.value = 'Oportunidade não encontrada'
  } finally {
    loading.value = false
  }
}

// Função para rastrear visita
const trackVisit = async () => {
  try {
    // Verificar se a visita já foi registrada via parâmetro de consulta
    if (route.query.tracked === '1') {
      console.log('Visita já registrada pelo backend, não registrando novamente');
      return;
    }
    
    // Verificar se o usuário veio do backend (redirecionamento)
    const referrer = document.referrer;
    if (referrer && referrer.includes(UrlBuilder.getApiBaseUrl())) {
      console.log('Visita já registrada pelo backend (referrer), não registrando novamente');
      return;
    }
    
    const slug = route.params.slug as string
    // Utilizando a rota da API
    const trackUrl = UrlBuilder.api(`track-visit/${slug}`)
    const response = await axios.get(trackUrl)
    console.log('Visita registrada:', response.data)
  } catch (err) {
    console.error('Erro ao registrar visita:', err)
  }
}

onMounted(async () => {
  console.log('🛠️ Iniciando fluxo de autenticação OAuth...');
  
  await loadOportunidade()
  trackVisit() // Registra a visita quando a página carrega
  
  // Gerar token reCAPTCHA para uso no formulário
  try {
    recaptchaToken.value = await getToken('candidatura_submit')
  } catch (err) {
    console.error('Erro ao obter token reCAPTCHA:', err)
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading -->
    <div v-if="loading" class="flex justify-center items-center min-h-screen">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="min-h-screen flex items-center justify-center px-4">
      <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L5.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ error }}</h3>
        <div class="mt-6">
          <router-link to="/admin/oportunidades" class="text-blue-600 hover:text-blue-800">
            ← Voltar para Oportunidades
          </router-link>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-else-if="oportunidade">
      <!-- Hero Section -->
      <div class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
          <div class="max-w-4xl">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ oportunidade.titulo }}</h1>
            <p class="text-xl opacity-90">Junte-se à nossa equipe e faça parte de algo incrível!</p>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Job Header -->
        <div class="bg-white rounded-lg shadow-lg p-8 -mt-8 relative z-10 mb-8">
          <div class="grid md:grid-cols-3 gap-8">
            <div class="md:col-span-2">
              <h2 class="text-2xl font-semibold text-gray-900 mb-4">Sobre a Oportunidade</h2>
              <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 whitespace-pre-wrap">{{ oportunidade.descricao }}</p>
              </div>
              
              <!-- Localização -->
              <div v-if="oportunidade.localizacao" class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Localização</h3>
                <div class="flex items-center text-gray-600">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  <span>{{ oportunidade.localizacao }}</span>
                </div>
              </div>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Skills Desejadas</h3>
              <div class="flex flex-wrap gap-2">
                <span 
                  v-for="(skill, idx) in oportunidade.skills_desejadas" 
                  :key="typeof skill === 'string' ? skill : idx"
                  class="inline-block bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full"
                >
                  {{ typeof skill === 'string' ? skill : skill.nome }}<span v-if="typeof skill !== 'string' && skill.peso != null"> ({{ skill.peso }}%)</span>
                </span>
              </div>
            </div>
          </div>
          
          <!-- Botões de Partilha -->
          <div class="mt-8 flex justify-center">
            <div class="w-full max-w-2xl">
              <ShareButtons
                :titulo="oportunidade.titulo"
                :descricao="oportunidade.descricao"
                :url="currentUrl"
                :slug="oportunidade.slug || ''"
              />
            </div>
          </div>
        </div>

        <!-- Formulário de Candidatura com Autenticação Social -->
        <div class="max-w-4xl mx-auto pb-16">
          <div class="text-center mb-8">
            <svg class="mx-auto h-12 w-12 text-blue-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
            <h3 class="text-2xl font-semibold text-gray-900">Candidatar-se a esta Vaga</h3>
          </div>

          <!-- Componente de Candidatura Rápida (1 clique) -->
          <CandidaturaRapida :oportunidade-id="oportunidade.id || 0">
            <template #traditional-form>
              <div class="bg-white rounded-lg shadow-lg p-8">
                <CandidaturaForm 
                  :oportunidade-id="oportunidade.id || 0" 
                  :recaptcha-token="recaptchaToken"
                />
              </div>
            </template>
          </CandidaturaRapida>
        </div>
      </div>
    </div>
    
    <!-- Componente de Debug OAuth -->
    <OAuthDebug />
  </div>
</template>
