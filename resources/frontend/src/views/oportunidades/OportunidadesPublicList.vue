<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
          Oportunidades de Carreira
        </h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
          Descubra vagas incríveis e dê o próximo passo na sua carreira profissional
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <p class="mt-2 text-gray-600">Carregando oportunidades...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
          <p class="text-red-600">{{ error }}</p>
          <button 
            @click="() => fetchOportunidades()" 
            class="mt-4 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors"
          >
            Tentar Novamente
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="oportunidades.length === 0" class="text-center py-12">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8">
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            Nenhuma oportunidade disponível
          </h3>
          <p class="text-gray-600">
            No momento não há oportunidades públicas disponíveis. Volte em breve!
          </p>
        </div>
      </div>

      <!-- Opportunities Grid -->
      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-1">
        <div 
          v-for="oportunidade in oportunidades" 
          :key="oportunidade.id"
          class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 border border-gray-200"
        >
          <div class="p-6">
            <!-- Header -->
            <div class="flex items-start justify-between mb-4">
              <h2 class="text-xl font-semibold text-gray-900 flex-1 pr-4">
                {{ oportunidade.titulo }}
              </h2>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Ativa
              </span>
            </div>

            <!-- Description -->
            <p class="text-gray-600 mb-6 line-clamp-3">
              {{ oportunidade.descricao }}
            </p>

            <!-- Skills -->
            <div v-if="oportunidade.skills_desejadas && oportunidade.skills_desejadas.length > 0" class="mb-6">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Skills Desejadas:</h4>
              <div class="flex flex-wrap gap-2">
                <span 
                  v-for="skill in oportunidade.skills_desejadas.slice(0, 6)" 
                  :key="(typeof skill === 'string' ? skill : skill.nome)"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                >
                  {{ typeof skill === 'string' ? skill : skill.nome }}
                </span>
                <span 
                  v-if="oportunidade.skills_desejadas.length > 6"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
                >
                  +{{ oportunidade.skills_desejadas.length - 6 }} mais
                </span>
              </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between">
              <div class="flex flex-col">
                <span class="text-sm text-gray-500">
                  Publicada em {{ formatDate(oportunidade.created_at || '') }}
                </span>
                <span v-if="oportunidade.localizacao" class="text-sm text-gray-600 flex items-center mt-1">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  {{ oportunidade.localizacao }}
                </span>
              </div>
              <router-link 
                :to="{ name: 'oportunidade.public', params: { slug: oportunidade.slug || '' } }"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
              >
                Ver Oportunidade
                <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.last_page > 1" class="mt-8 flex justify-center">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Anterior
          </button>
          
          <span
            v-for="page in visiblePages"
            :key="page"
            @click="changePage(page)"
            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium cursor-pointer"
            :class="page === pagination.current_page 
              ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' 
              : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
          >
            {{ page }}
          </span>
          
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Próxima
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { oportunidadesService, type Oportunidade as OportunidadeType, type PaginatedResponse } from '../../services/api'

interface Skill {
  nome: string
  peso?: number
}

interface Oportunidade {
  id?: number
  titulo: string
  descricao: string
  skills_desejadas: (string | Skill)[]
  localizacao?: string
  slug?: string
  ativa?: boolean
  publica?: boolean
  created_at?: string
}

interface PaginationData {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

const oportunidades = ref<Oportunidade[]>([])
const pagination = ref<PaginationData | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const visiblePages = computed(() => {
  if (!pagination.value) return []
  
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  const pages = []
  
  let start = Math.max(1, current - 2)
  let end = Math.min(last, current + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const fetchOportunidades = async (page = 1) => {
  try {
    loading.value = true
    error.value = null
    
    console.log('Fetching oportunidades from page:', page)
    const response = await oportunidadesService.getPublic(page)
    console.log('Response received:', response)
    
    oportunidades.value = response.data || []
    console.log('Oportunidades set:', oportunidades.value)
    
    pagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      per_page: response.per_page,
      total: response.total
    }
    console.log('Pagination set:', pagination.value)
  } catch (err) {
    console.error('Erro ao carregar oportunidades:', err)
    error.value = 'Erro ao carregar as oportunidades. Tente novamente.'
  } finally {
    loading.value = false
  }
}

const changePage = (page: number) => {
  if (page >= 1 && pagination.value && page <= pagination.value.last_page) {
    fetchOportunidades(page)
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(() => {
  fetchOportunidades()
})
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
