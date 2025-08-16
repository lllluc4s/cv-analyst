<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center">
          <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">
            Empresas Parceiras
          </h1>
          <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500">
            Descubra as empresas que estão a recrutar e explore as suas oportunidades
          </p>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="text-center">
        <div class="text-red-500 text-lg mb-4">{{ error }}</div>
        <router-link to="/oportunidades" class="text-indigo-600 hover:text-indigo-800">
          ← Voltar para oportunidades
        </router-link>
      </div>
    </div>

    <!-- Companies Grid -->
    <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="companies.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2M7 21h2m3-18v18m3 0v-8a2 2 0 012-2h2a2 2 0 012 2v8M13 3h6v6M9 9h1m0 0v1m0-1h1m-2 4h1m0 0v1m0-1h1" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma empresa encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">Ainda não há empresas com oportunidades públicas.</p>
      </div>

      <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <div 
          v-for="company in companies" 
          :key="company.id"
          class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 border border-gray-200 overflow-hidden group"
        >
          <router-link :to="{ name: 'empresa.public', params: { slug: company.slug } }">
            <div class="p-6">
              <!-- Company Logo and Header -->
              <div class="flex items-center space-x-4 mb-4">
                <div v-if="company.logo_url" class="flex-shrink-0">
                  <img 
                    :src="company.logo_url" 
                    :alt="`Logo da ${company.name}`"
                    class="h-12 w-12 rounded-lg object-contain border border-gray-200 bg-white"
                  />
                </div>
                <div v-else class="flex-shrink-0">
                  <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-200 flex items-center justify-center border border-indigo-200">
                    <svg class="h-6 w-6 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                    </svg>
                  </div>
                </div>
                
                <div class="flex-1 min-w-0">
                  <h3 class="text-lg font-semibold text-gray-900 truncate group-hover:text-indigo-600 transition-colors">
                    {{ company.name }}
                  </h3>
                  <p v-if="company.sector" class="text-sm text-gray-500 truncate">
                    {{ company.sector }}
                  </p>
                </div>
              </div>

              <!-- Company Stats -->
              <div class="space-y-3">
                
                <div v-if="company.website" class="flex items-center">
                  <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                  <span class="text-sm text-gray-500 truncate">
                    {{ company.website.replace(/^https?:\/\//, '') }}
                  </span>
                </div>
              </div>

              <!-- Call to Action -->
              <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="text-center">
                  <span class="inline-flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                    Ver oportunidades
                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </span>
                </div>
              </div>
            </div>
          </router-link>
        </div>
      </div>

      <!-- Back to Opportunities Link -->
      <div class="mt-12 text-center">
        <router-link 
          to="/oportunidades"
          class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors"
        >
          <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Ver todas as oportunidades
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

interface Company {
  id: number
  name: string
  slug: string
  logo_url?: string
  sector?: string
  website?: string
  oportunidades_count: number
  page_url: string
}

const loading = ref(true)
const error = ref('')
const companies = ref<Company[]>([])

const loadCompanies = async () => {
  try {
    loading.value = true
    error.value = ''
    
    const response = await axios.get('/public/empresas')
    companies.value = response.data
    
  } catch (err: any) {
    console.error('Erro ao carregar empresas:', err)
    error.value = 'Erro ao carregar lista de empresas'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadCompanies()
})
</script>
