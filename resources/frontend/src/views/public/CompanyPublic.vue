<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center min-h-screen">
      <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex justify-center items-center min-h-screen">
      <div class="text-center">
        <div class="text-red-500 text-xl mb-4">{{ error }}</div>
        <router-link to="/oportunidades" class="text-indigo-600 hover:text-indigo-800">
          ← Voltar para listagem de oportunidades
        </router-link>
      </div>
    </div>

    <!-- Company Page Content -->
    <div v-else-if="company">
      <!-- Breadcrumb -->
      <nav class="bg-gray-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
          <div class="flex items-center space-x-2 text-sm">
            <router-link to="/oportunidades" class="text-indigo-600 hover:text-indigo-800 font-medium">
              Oportunidades
            </router-link>
            <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
            <span class="text-gray-500">{{ company.name }}</span>
          </div>
        </div>
      </nav>

      <!-- Hero Section -->
      <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div class="lg:flex lg:items-center lg:justify-between">
            <div class="flex-1 min-w-0">
              <div class="flex items-center">
                <!-- Company Logo -->
                <div v-if="company.logo_url" class="flex-shrink-0 mr-6">
                  <img 
                    :src="company.logo_url" 
                    :alt="`Logo da ${company.name}`"
                    class="h-20 w-20 rounded-xl object-contain border-2 border-gray-200 bg-white shadow-sm"
                  />
                </div>
                <div v-else class="flex-shrink-0 mr-6">
                  <div class="h-20 w-20 rounded-xl bg-gradient-to-br from-indigo-100 to-indigo-200 flex items-center justify-center border-2 border-indigo-200 shadow-sm">
                    <svg class="h-10 w-10 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                    </svg>
                  </div>
                </div>
                
                <div>
                  <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ company.name }}</h1>
                  <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                    <div v-if="company.sector" class="mt-2 flex items-center text-sm text-gray-500">
                      <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                        <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" />
                      </svg>
                      {{ company.sector }}
                    </div>
                    <div v-if="company.website" class="mt-2 flex items-center text-sm text-gray-500">
                      <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16 8 8 0 000-16zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.559-.499-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.559.499.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.497-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd" />
                      </svg>
                      <a :href="company.website" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                        {{ company.website.replace(/^https?:\/\//, '') }}
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-5 flex lg:mt-0 lg:ml-4">
              <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                {{ company.total_oportunidades }} oportunidade{{ company.total_oportunidades !== 1 ? 's' : '' }} ativa{{ company.total_oportunidades !== 1 ? 's' : '' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Opportunities List -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div v-if="oportunidades.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
            <path d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A10.003 10.003 0 0124 26c4.21 0 7.813 2.602 9.288 6.286M30 14a6 6 0 11-12 0 6 6 0 0112 0zm12 6a4 4 0 11-8 0 4 4 0 018 0zm-28 0a4 4 0 11-8 0 4 4 0 018 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma oportunidade disponível</h3>
          <p class="mt-1 text-sm text-gray-500">Esta empresa ainda não publicou oportunidades.</p>
        </div>

        <div v-else class="grid gap-6 lg:grid-cols-1">
          <div 
            v-for="oportunidade in oportunidades" 
            :key="oportunidade.id"
            class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 border border-gray-200"
          >
            <div class="p-6">
              <!-- Header -->
              <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                  <h2 class="text-xl font-semibold text-gray-900 pr-4">
                    {{ oportunidade.titulo }}
                  </h2>
                  <div class="mt-1 flex items-center text-sm text-gray-500">
                    <span>Publicada em {{ formatDate(oportunidade.created_at) }}</span>
                  </div>
                </div>
                <div class="flex items-center">
                  <div class="flex items-center space-x-2">
                    <img 
                      v-if="oportunidade.company.logo_url" 
                      :src="oportunidade.company.logo_url" 
                      :alt="`Logo da ${oportunidade.company.name}`"
                      class="h-12 w-12 rounded-lg object-contain border border-gray-200 bg-white"
                    />
                    <div v-else class="h-12 w-12 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center border border-gray-200">
                      <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">{{ oportunidade.company.name }}</span>
                  </div>
                </div>
              </div>

              <!-- Description -->
              <p class="text-gray-600 mb-4 line-clamp-3">
                {{ oportunidade.descricao }}
              </p>

              <!-- Skills -->
              <div v-if="oportunidade.skills_desejadas?.length > 0" class="mb-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Skills requeridas:</h4>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="(skill, index) in oportunidade.skills_desejadas.slice(0, 8)"
                    :key="index"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 border border-indigo-200"
                  >
                    {{ typeof skill === 'object' ? skill.nome : skill }}
                  </span>
                  <span 
                    v-if="oportunidade.skills_desejadas.length > 8"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600 border border-gray-200"
                  >
                    +{{ oportunidade.skills_desejadas.length - 8 }} mais
                  </span>
                </div>
              </div>

              <!-- Footer -->
              <div class="flex items-center justify-between">
                <div class="flex flex-col">
                  <span v-if="oportunidade.localizacao" class="text-sm text-gray-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ oportunidade.localizacao }}
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
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

interface Company {
  id: number
  name: string
  website?: string
  sector?: string
  logo_url?: string
  total_oportunidades: number
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
    name: string
    logo_url?: string
    website?: string
    sector?: string
  }
}

const route = useRoute()
const loading = ref(true)
const error = ref('')
const company = ref<Company | null>(null)
const oportunidades = ref<Oportunidade[]>([])

const loadCompanyData = async () => {
  try {
    loading.value = true
    error.value = ''
    
    const slug = route.params.slug as string
    const response = await axios.get(`/public/empresa/${slug}`)
    
    company.value = response.data.company
    oportunidades.value = response.data.oportunidades
    
  } catch (err: any) {
    console.error('Erro ao carregar dados da empresa:', err)
    if (err.response?.status === 404) {
      error.value = 'Empresa não encontrada'
    } else {
      error.value = 'Erro ao carregar dados da empresa'
    }
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-PT', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(() => {
  loadCompanyData()
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
