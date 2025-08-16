<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
            Minhas Candidaturas
          </h3>
          
          <div v-if="loading" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Carregando candidaturas...</p>
          </div>
          
          <div v-else-if="error" class="text-center py-8">
            <p class="text-red-600">{{ error }}</p>
            <button @click="reloadCandidaturas" class="mt-2 btn btn-primary">
              Tentar novamente
            </button>
          </div>
          
          <div v-else-if="candidaturas.length === 0" class="text-center py-8">
            <div class="text-gray-400 mb-4">
              <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma candidatura encontrada</h3>
            <p class="text-gray-600 mb-4">Você ainda não se candidatou a nenhuma oportunidade.</p>
            <router-link 
              to="/oportunidades" 
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
            >
              Ver Oportunidades
            </router-link>
          </div>
          
          <div v-else class="space-y-4">
            <div v-for="candidatura in candidaturas" :key="candidatura.id" 
                 class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h4 class="text-lg font-medium text-gray-900 mb-2">
                    {{ candidatura.oportunidade?.titulo || 'Oportunidade não encontrada' }}
                  </h4>
                  
                  <div class="space-y-2 text-sm text-gray-600">
                    <div v-if="candidatura.oportunidade?.company" class="flex items-center">
                      <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                      {{ candidatura.oportunidade.company.name }}
                    </div>
                    
                    <div v-if="candidatura.oportunidade?.localizacao" class="flex items-center">
                      <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      {{ candidatura.oportunidade.localizacao }}
                    </div>
                    
                    <div class="flex items-center">
                      <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      Candidatura em: {{ formatDate(candidatura.created_at) }}
                    </div>
                  </div>
                  
                  <div v-if="candidatura.oportunidade?.descricao" class="mt-3">
                    <p class="text-sm text-gray-700 line-clamp-3">
                      {{ candidatura.oportunidade.descricao }}
                    </p>
                  </div>
                </div>
                
                <div class="ml-6 flex flex-col items-end space-y-2">
                  <span :class="getStatusClass(candidatura.status)" 
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ getStatusText(candidatura.status) }}
                  </span>
                  
                  <div v-if="candidatura.oportunidade" class="flex space-x-2">
                    <router-link 
                      :to="`/oportunidade/${candidatura.oportunidade.slug}`"
                      class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50"
                    >
                      Ver Oportunidade
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Paginação -->
            <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
              <div class="flex flex-1 justify-between sm:hidden">
                <button 
                  @click="goToPage(meta.current_page - 1)"
                  :disabled="meta.current_page <= 1"
                  class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                >
                  Anterior
                </button>
                <button 
                  @click="goToPage(meta.current_page + 1)"
                  :disabled="meta.current_page >= meta.last_page"
                  class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                >
                  Próximo
                </button>
              </div>
              <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Mostrando <span class="font-medium">{{ meta.from }}</span> a <span class="font-medium">{{ meta.to }}</span> de <span class="font-medium">{{ meta.total }}</span> candidaturas
                  </p>
                </div>
                <div>
                  <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <button 
                      @click="goToPage(meta.current_page - 1)"
                      :disabled="meta.current_page <= 1"
                      class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
                    >
                      <span class="sr-only">Anterior</span>
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                      </svg>
                    </button>
                    
                    <button 
                      v-for="page in getVisiblePages()" 
                      :key="page"
                      @click="typeof page === 'number' ? goToPage(page) : undefined"
                      :disabled="typeof page !== 'number'"
                      :class="page === meta.current_page ? 'bg-blue-600 text-white' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'"
                      class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20 focus:outline-offset-0 disabled:cursor-default"
                    >
                      {{ page }}
                    </button>
                    
                    <button 
                      @click="goToPage(meta.current_page + 1)"
                      :disabled="meta.current_page >= meta.last_page"
                      class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
                    >
                      <span class="sr-only">Próximo</span>
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { candidatoAuthService } from '../../services/candidatoAuth'

const loading = ref(true)
const error = ref('')
const candidaturas = ref<any[]>([])
const meta = ref<any>(null)

const loadCandidaturas = async (page = 1) => {
  loading.value = true
  error.value = ''
  
  try {
    const response = await candidatoAuthService.getCandidaturas(page)
    candidaturas.value = response.data
    meta.value = response.meta
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro ao carregar candidaturas'
  } finally {
    loading.value = false
  }
}

const reloadCandidaturas = () => {
  loadCandidaturas(1)
}

const goToPage = (page: number | string) => {
  const pageNumber = typeof page === 'number' ? page : parseInt(page.toString())
  if (pageNumber >= 1 && pageNumber <= meta.value.last_page && !isNaN(pageNumber)) {
    loadCandidaturas(pageNumber)
  }
}

const getVisiblePages = () => {
  if (!meta.value) return []
  
  const current = meta.value.current_page
  const last = meta.value.last_page
  const pages = []
  
  // Sempre mostrar primeira página
  if (current > 3) {
    pages.push(1)
    if (current > 4) {
      pages.push('...')
    }
  }
  
  // Páginas ao redor da atual
  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i)
  }
  
  // Sempre mostrar última página
  if (current < last - 2) {
    if (current < last - 3) {
      pages.push('...')
    }
    pages.push(last)
  }
  
  return pages.filter((page, index, arr) => page !== arr[index - 1])
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusClass = (status: string) => {
  switch (status) {
    case 'pendente':
      return 'bg-yellow-100 text-yellow-800'
    case 'aceito':
      return 'bg-green-100 text-green-800'
    case 'rejeitado':
      return 'bg-red-100 text-red-800'
    case 'em_analise':
      return 'bg-blue-100 text-blue-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getStatusText = (status: string) => {
  switch (status) {
    case 'pendente':
      return 'Pendente'
    case 'aceito':
      return 'Aceito'
    case 'rejeitado':
      return 'Rejeitado'
    case 'em_analise':
      return 'Em Análise'
    default:
      return status
  }
}

onMounted(() => {
  loadCandidaturas()
})
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
