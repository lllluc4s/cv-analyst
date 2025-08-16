<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { oportunidadesService, type Oportunidade, type PaginatedResponse } from '../../services/api'

const router = useRouter()

const oportunidades = ref<PaginatedResponse<Oportunidade> | null>(null)
const loading = ref(true)
const error = ref('')

const loadOportunidades = async (page = 1) => {
  try {
    loading.value = true
    error.value = ''
    oportunidades.value = await oportunidadesService.getAll(page)
  } catch (err) {
    error.value = 'Erro ao carregar oportunidades. Verifique se o backend está rodando.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const deleteOportunidade = async (slug: string) => {
  if (!confirm('Tem certeza que deseja excluir esta oportunidade?')) {
    return
  }
  
  try {
    await oportunidadesService.delete(slug)
    await loadOportunidades()
  } catch (err) {
    error.value = 'Erro ao excluir oportunidade'
    console.error(err)
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR')
}

const generateSlug = (title: string) => {
  return title.toLowerCase().replace(/\s+/g, '-')
}

const viewCandidatosPotencial = (oportunidade: Oportunidade) => {
  router.push({ 
    name: 'company.candidatosPotencial', 
    params: { id: oportunidade.id } 
  })
}

onMounted(() => {
  loadOportunidades()
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Oportunidades</h1>
        <p class="text-gray-600 mt-1">Gerencie suas vagas de emprego</p>
      </div>
      <RouterLink
        to="/admin/oportunidades/create"
        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Nova Oportunidade
      </RouterLink>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
      <div class="flex">
        <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        <div>
          <h3 class="text-sm font-medium text-red-800">Erro</h3>
          <div class="mt-1 text-sm text-red-700">{{ error }}</div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!oportunidades?.data.length" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0H8m8 0v2a2 2 0 01-2 2H10a2 2 0 01-2-2V6"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma oportunidade encontrada</h3>
      <p class="mt-1 text-sm text-gray-500">Comece criando uma nova oportunidade.</p>
      <div class="mt-6">
        <RouterLink
          to="/admin/oportunidades/create"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Nova Oportunidade
        </RouterLink>
      </div>
    </div>

    <!-- Oportunidades List -->
    <div v-else class="space-y-4">
      <div 
        v-for="oportunidade in oportunidades?.data" 
        :key="oportunidade.id"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
      >
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
              {{ oportunidade.titulo }}
            </h3>
            <p class="text-gray-600 mb-4 line-clamp-3">
              {{ oportunidade.descricao }}
            </p>
            
            <!-- Skills -->
            <div class="mb-4">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Skills Desejadas:</h4>
              <div class="flex flex-wrap gap-2">
                <span 
                  v-for="(skill, idx) in oportunidade.skills_desejadas" 
                  :key="typeof skill === 'string' ? skill : (skill.nome || idx)"
                  class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full"
                >
                  {{ typeof skill === 'string' ? skill : skill.nome }}
                  <span v-if="typeof skill !== 'string' && skill.peso != null"> ({{ skill.peso }}%)</span>
                </span>
              </div>
            </div>

            <!-- Meta info -->
            <div class="text-sm text-gray-500 flex items-center gap-4 flex-wrap">
              <span class="inline-flex items-center">
                Criada em {{ formatDate(oportunidade.created_at!) }}
              </span>
              <span v-if="oportunidade.localizacao" class="inline-flex items-center text-gray-600">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                {{ oportunidade.localizacao }}
              </span>
              <span v-if="oportunidade.ativa" class="inline-flex items-center text-green-600">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Ativa
                <!-- Ícone de listada publicamente -->
                <svg v-if="oportunidade.publica" class="w-4 h-4 ml-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span v-if="oportunidade.publica" class="ml-1 text-xs text-blue-600">Listada</span>
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col gap-2 ml-4">
            <RouterLink
              :to="{ name: 'oportunidades.show', params: { slug: oportunidade.slug } }"
              class="text-blue-600 hover:text-blue-800 text-sm font-medium"
            >
              Ver Detalhes
            </RouterLink>
            <button
              @click="viewCandidatosPotencial(oportunidade)"
              class="text-purple-600 hover:text-purple-800 text-sm font-medium text-left"
            >
              Candidatos com Potencial
            </button>
            <RouterLink
              :to="{ name: 'oportunidades.edit', params: { slug: oportunidade.slug } }"
              class="text-indigo-600 hover:text-indigo-800 text-sm font-medium"
            >
              Editar
            </RouterLink>
            <button
              @click="deleteOportunidade(oportunidade.slug!)"
              class="text-red-600 hover:text-red-800 text-sm font-medium text-left"
            >
              Excluir
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="oportunidades && oportunidades.last_page > 1" class="mt-8">
      <nav class="flex justify-center">
        <div class="flex space-x-2">
          <button
            v-for="page in oportunidades.last_page"
            :key="page"
            @click="loadOportunidades(page)"
            :class="[
              'px-3 py-2 text-sm font-medium rounded-md',
              page === oportunidades.current_page
                ? 'bg-blue-600 text-white'
                : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </nav>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
