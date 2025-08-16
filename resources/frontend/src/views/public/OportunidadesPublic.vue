<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="lg:flex lg:items-center lg:justify-between">
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">
              Oportunidades de Emprego
            </h1>
            <p class="mt-1 text-sm text-gray-500">
              Descubra as melhores oportunidades de carreira em empresas inovadoras
            </p>
          </div>
          <div class="mt-5 flex lg:mt-0 lg:ml-4">
            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
              {{ totalOportunidades }} oportunidade{{ totalOportunidades !== 1 ? 's' : '' }} encontrada{{ totalOportunidades !== 1 ? 's' : '' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="lg:grid lg:grid-cols-4 lg:gap-8">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 lg:mb-0">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Filtros</h2>
            
            <!-- Company Filter -->
            <div class="mb-6">
              <label for="company" class="block text-sm font-medium text-gray-700 mb-2">
                Empresa
              </label>
              <select
                id="company"
                v-model="selectedCompany"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                @change="applyFilters"
              >
                <option value="">Todas as empresas</option>
                <option v-for="company in companies" :key="company.id" :value="company.id">
                  {{ company.name }}
                </option>
              </select>
            </div>

            <!-- Skills Filter -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Skills
              </label>
              <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-md p-3">
                <div
                  v-for="skill in availableSkills"
                  :key="skill"
                  class="flex items-center"
                >
                  <input
                    :id="`skill-${skill}`"
                    v-model="selectedSkills"
                    type="checkbox"
                    :value="skill"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    @change="applyFilters"
                  />
                  <label :for="`skill-${skill}`" class="ml-2 text-sm text-gray-700">
                    {{ skill }}
                  </label>
                </div>
              </div>
              
              <!-- Selected Skills Display -->
              <div v-if="selectedSkills.length > 0" class="mt-3 flex flex-wrap gap-2">
                <span
                  v-for="skill in selectedSkills"
                  :key="skill"
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                >
                  {{ skill }}
                  <button
                    @click="removeSkill(skill)"
                    class="ml-1 text-indigo-600 hover:text-indigo-800"
                  >
                    ×
                  </button>
                </span>
              </div>
            </div>

            <!-- Clear Filters -->
            <button
              @click="clearFilters"
              class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
              Limpar Filtros
            </button>
          </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3">
          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="text-red-700">{{ error }}</div>
          </div>

          <!-- No Results -->
          <div v-else-if="oportunidades.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
              <path d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A10.003 10.003 0 0124 26c4.21 0 7.813 2.602 9.288 6.286M30 14a6 6 0 11-12 0 6 6 0 0112 0zm12 6a4 4 0 11-8 0 4 4 0 018 0zm-28 0a4 4 0 11-8 0 4 4 0 018 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma oportunidade encontrada</h3>
            <p class="mt-1 text-sm text-gray-500">Tente ajustar os filtros para encontrar mais resultados.</p>
          </div>

          <!-- Opportunities Grid -->
          <div v-else class="grid gap-6">
            <div 
              v-for="oportunidade in oportunidades" 
              :key="oportunidade.id"
              class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 border border-gray-200"
            >
              <div class="p-6">
                <!-- Header with Company Info -->
                <div class="flex items-start justify-between mb-4">
                  <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-900 pr-4">
                      {{ oportunidade.titulo }}
                    </h2>
                    <div class="mt-1 flex items-center">
                      <div v-if="oportunidade.company" class="flex items-center text-sm text-gray-600">
                        <img 
                          v-if="oportunidade.company.logo_url" 
                          :src="oportunidade.company.logo_url" 
                          :alt="`Logo da ${oportunidade.company.name}`"
                          class="h-5 w-5 rounded mr-2 object-contain"
                        />
                        <div v-else class="h-5 w-5 rounded bg-gray-200 flex items-center justify-center mr-2">
                          <svg class="h-3 w-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                          </svg>
                        </div>
                        <router-link 
                          :to="{ name: 'empresa.public', params: { slug: getCompanySlug(oportunidade.company.name) } }"
                          class="hover:text-indigo-600 transition-colors cursor-pointer"
                          :title="`Ver todas as oportunidades da ${oportunidade.company.name}`"
                        >
                          {{ oportunidade.company.name }}
                        </router-link>
                        <span v-if="oportunidade.company.sector" class="ml-2 text-xs text-gray-500">
                          • {{ oportunidade.company.sector }}
                        </span>
                      </div>
                      <div v-else class="flex items-center text-sm text-gray-600">
                        <div class="h-5 w-5 rounded bg-gray-200 flex items-center justify-center mr-2">
                          <svg class="h-3 w-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                          </svg>
                        </div>
                        <span>Empresa não especificada</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Description -->
                <p class="text-gray-600 mb-4 line-clamp-3">
                  {{ oportunidade.descricao }}
                </p>

                <!-- Skills -->
                <div v-if="oportunidade.skills_desejadas?.length > 0" class="mb-4">
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="(skill, index) in oportunidade.skills_desejadas.slice(0, 6)"
                      :key="index"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                    >
                      {{ typeof skill === 'object' ? skill.nome : skill }}
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
                  <div class="flex flex-col space-y-1">
                    <span v-if="oportunidade.localizacao" class="text-sm text-gray-600 flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                      {{ oportunidade.localizacao }}
                    </span>
                    <span class="text-xs text-gray-500">
                      Publicada em {{ formatDate(oportunidade.created_at) }}
                    </span>
                  </div>
                  <router-link 
                    :to="{ name: 'oportunidade.public', params: { slug: oportunidade.slug } }"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                  >
                    Ver Oportunidade
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </router-link>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="pagination && pagination.last_page > 1" class="mt-8 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button
                :disabled="pagination.current_page === 1"
                @click="changePage(pagination.current_page - 1)"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Anterior
              </button>
              
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="changePage(page)"
                :class="[
                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                  page === pagination.current_page
                    ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                ]"
              >
                {{ page }}
              </button>
              
              <button
                :disabled="pagination.current_page === pagination.last_page"
                @click="changePage(pagination.current_page + 1)"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Próxima
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

interface Company {
  id: number
  name: string
  logo_url?: string
  sector?: string
  oportunidades_count: number
}

interface Oportunidade {
  id: number
  titulo: string
  descricao: string
  skills_desejadas: Array<{ nome: string; peso: number } | string>
  localizacao?: string
  slug: string
  created_at: string
  company: {
    id: number
    name: string
    logo_url?: string
    website?: string
    sector?: string
  }
}

interface PaginationData {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const oportunidades = ref<Oportunidade[]>([])
const companies = ref<Company[]>([])
const availableSkills = ref<string[]>([])
const pagination = ref<PaginationData | null>(null)

// Filters
const selectedCompany = ref('')
const selectedSkills = ref<string[]>([])
const currentPage = ref(1)

// Computed
const totalOportunidades = computed(() => pagination.value?.total || 0)

const visiblePages = computed(() => {
  if (!pagination.value) return []
  
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  const pages = []
  
  // Show up to 5 pages
  let start = Math.max(1, current - 2)
  let end = Math.min(last, start + 4)
  
  if (end - start < 4) {
    start = Math.max(1, end - 4)
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

// Methods
const loadOportunidades = async () => {
  try {
    console.log('loadOportunidades: Starting...')
    loading.value = true
    error.value = ''
    
    const params = new URLSearchParams()
    
    if (selectedCompany.value) {
      params.append('company_id', selectedCompany.value)
    }
    if (selectedSkills.value.length > 0) {
      params.append('skills', selectedSkills.value.join(','))
    }
    if (currentPage.value > 1) {
      params.append('page', currentPage.value.toString())
    }
    
    console.log('loadOportunidades: Making request to:', `/public/oportunidades?${params.toString()}`)
    console.log('loadOportunidades: Selected skills:', selectedSkills.value)
    console.log('loadOportunidades: Selected company:', selectedCompany.value)
    const response = await axios.get(`/public/oportunidades?${params.toString()}`)
    console.log('loadOportunidades: Response received:', response.data)
    
    if (response.data && response.data.data) {
      oportunidades.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total
      }
      console.log('loadOportunidades: Set oportunidades:', oportunidades.value.length)
    } else {
      console.error('loadOportunidades: Invalid response structure:', response.data)
      oportunidades.value = []
    }
    
  } catch (err: any) {
    console.error('Erro ao carregar oportunidades:', err)
    error.value = 'Erro ao carregar oportunidades: ' + (err.message || 'Erro desconhecido')
  } finally {
    loading.value = false
  }
}

const loadFilterOptions = async () => {
  try {
    console.log('loadFilterOptions: Starting...')
    const [companiesResponse, skillsResponse] = await Promise.all([
      axios.get('/public/companies'),
      axios.get('/public/skills')
    ])
    
    console.log('loadFilterOptions: Companies:', companiesResponse.data)
    console.log('loadFilterOptions: Skills:', skillsResponse.data)
    
    companies.value = companiesResponse.data
    availableSkills.value = skillsResponse.data
    
  } catch (err) {
    console.error('Erro ao carregar opções de filtro:', err)
  }
}

const applyFilters = () => {
  currentPage.value = 1
  loadOportunidades()
}

const clearFilters = () => {
  selectedCompany.value = ''
  selectedSkills.value = []
  currentPage.value = 1
  loadOportunidades()
}

const removeSkill = (skill: string) => {
  selectedSkills.value = selectedSkills.value.filter(s => s !== skill)
  applyFilters()
}

const changePage = (page: number) => {
  currentPage.value = page
  loadOportunidades()
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-PT', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getCompanySlug = (companyName: string) => {
  return companyName.toLowerCase().replace(/\s+/g, '-')
}

// Watch route params - Temporarily disabled for debugging
/*
watch(() => route.query, (newQuery) => {
  if (newQuery.company) {
    selectedCompany.value = newQuery.company as string
  }
  if (newQuery.location) {
    selectedLocation.value = newQuery.location as string
  }
  if (newQuery.page) {
    currentPage.value = parseInt(newQuery.page as string) || 1
  }
}, { immediate: true })
*/

onMounted(async () => {
  console.log('OportunidadesPublic: Component mounted')
  try {
    await loadFilterOptions()
    console.log('OportunidadesPublic: Filter options loaded')
    await loadOportunidades()
    console.log('OportunidadesPublic: Opportunities loaded')
  } catch (error) {
    console.error('OportunidadesPublic: Error in onMounted:', error)
  }
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
