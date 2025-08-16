<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">
          Oportunidades de Emprego - Teste
        </h1>
        <p class="mt-1 text-sm text-gray-500">
          Debug mode: {{ loading ? 'Carregando...' : 'Carregado' }}
        </p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading state -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Carregando oportunidades...</p>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="text-center py-12">
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
          <p class="text-red-600">{{ error }}</p>
        </div>
      </div>

      <!-- Content -->
      <div v-else>
        <div class="mb-6">
          <p class="text-gray-600">Total de oportunidades: {{ oportunidades.length }}</p>
        </div>

        <!-- Simple list of opportunities -->
        <div class="space-y-4">
          <div 
            v-for="oportunidade in oportunidades" 
            :key="oportunidade.id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
          >
            <h3 class="text-lg font-semibold text-gray-900">{{ oportunidade.titulo }}</h3>
            <p class="text-gray-600 mt-1">{{ oportunidade.company.name }}</p>
            <p class="text-gray-500 mt-1">{{ oportunidade.localizacao }}</p>
          </div>
        </div>

        <!-- No results -->
        <div v-if="oportunidades.length === 0" class="text-center py-12">
          <p class="text-gray-500">Nenhuma oportunidade encontrada.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

// Types
interface Company {
  id: number
  name: string
  logo_url: string | null
  website: string
  sector: string
}

interface Oportunidade {
  id: number
  titulo: string
  descricao: string
  skills_desejadas: Array<{ nome: string; peso: number }>
  localizacao: string
  slug: string
  created_at: string
  company: Company
}

// State
const loading = ref(false)
const error = ref('')
const oportunidades = ref<Oportunidade[]>([])

// Methods
const loadOportunidades = async () => {
  try {
    console.log('loadOportunidades: Starting...')
    loading.value = true
    error.value = ''
    
    const response = await axios.get('/public/oportunidades')
    console.log('loadOportunidades: Response received:', response.data)
    
    oportunidades.value = response.data.data || []
    
  } catch (err: any) {
    console.error('Erro ao carregar oportunidades:', err)
    error.value = 'Erro ao carregar oportunidades: ' + (err.message || 'Erro desconhecido')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  console.log('OportunidadesPublic: Component mounted')
  try {
    await loadOportunidades()
    console.log('OportunidadesPublic: Opportunities loaded')
  } catch (error) {
    console.error('OportunidadesPublic: Error in onMounted:', error)
  }
})
</script>
