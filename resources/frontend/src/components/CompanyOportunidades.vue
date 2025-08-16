<template>
  <div class="space-y-6">
    <!-- Header with Create Button -->
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-gray-900">Gestão de Oportunidades</h2>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
      >
        Nova Oportunidade
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin mx-auto h-8 w-8 text-blue-600">
        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
      <p class="mt-2 text-gray-500">A carregar oportunidades...</p>
    </div>

    <!-- Opportunities List -->
    <div v-else-if="oportunidades.length > 0" class="bg-white shadow overflow-hidden sm:rounded-md">
      <ul class="divide-y divide-gray-200">
        <li v-for="oportunidade in oportunidades" :key="oportunidade.id" class="px-6 py-4">
          <div class="space-y-3">
            <!-- Header with title and badges -->
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <div class="flex items-center">
                  <h3 class="text-lg font-medium text-gray-900">
                    {{ oportunidade.titulo }}
                  </h3>
                  <div class="ml-3 flex space-x-2">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        oportunidade.ativa
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ oportunidade.ativa ? 'Ativa' : 'Inativa' }}
                    </span>
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        oportunidade.publica
                          ? 'bg-blue-100 text-blue-800'
                          : 'bg-gray-100 text-gray-800'
                      ]"
                    >
                      {{ oportunidade.publica ? 'Pública' : 'Privada' }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="flex space-x-2 ml-4">
                <button
                  @click="editOportunidade(oportunidade)"
                  class="text-gray-600 hover:text-gray-500 text-sm font-medium"
                >
                  Editar
                </button>
                <button
                  @click="deleteOportunidade(oportunidade)"
                  class="text-red-600 hover:text-red-500 text-sm font-medium"
                >
                  Eliminar
                </button>
              </div>
            </div>
            
            <!-- Description -->
            <p class="text-sm text-gray-600 line-clamp-2">
              {{ oportunidade.descricao }}
            </p>
            
            <!-- Stats -->
            <div class="flex items-center text-sm text-gray-500">
              <span class="mr-4">
                {{ oportunidade.candidaturas_count || 0 }} candidatura(s)
              </span>
              <span>
                Criada em {{ formatDate(oportunidade.created_at) }}
              </span>
            </div>
            
            <!-- Actions -->
            <div class="flex flex-col gap-2">
              <button
                @click="viewCandidates(oportunidade)"
                class="text-blue-600 hover:text-blue-500 text-sm font-medium text-left"
              >
                Ver Detalhes
              </button>
              <button
                @click="viewCandidatosPotencial(oportunidade)"
                class="text-purple-600 hover:text-purple-500 text-sm font-medium text-left"
              >
                Candidatos com Potencial
              </button>
              <button
                @click="viewReports(oportunidade)"
                class="text-green-600 hover:text-green-500 text-sm font-medium text-left"
              >
                Relatórios
              </button>
            </div>
          </div>
        </li>
      </ul>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma oportunidade</h3>
      <p class="mt-1 text-sm text-gray-500">Comece criando a sua primeira oportunidade de trabalho.</p>
      <div class="mt-6">
        <button
          @click="showCreateModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
        >
          Nova Oportunidade
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 text-center">
            {{ showCreateModal ? 'Nova Oportunidade' : 'Editar Oportunidade' }}
          </h3>
          <form @submit.prevent="submitForm" class="mt-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Título *</label>
              <input
                v-model="form.titulo"
                type="text"
                required
                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="Ex: Desenvolvedor Backend PHP"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Descrição *</label>
              <textarea
                v-model="form.descricao"
                rows="4"
                required
                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="Descreva a oportunidade, responsabilidades, requisitos..."
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Skills Desejadas *</label>
              <div class="mt-1 flex">
                <input
                  v-model="newSkill"
                  type="text"
                  class="flex-1 border border-gray-300 rounded-l-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Ex: PHP, Laravel, MySQL"
                  @keyup.enter="addSkill"
                />
                <button
                  type="button"
                  @click="addSkill"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-md text-sm"
                >
                  Adicionar
                </button>
              </div>
              <div v-if="form.skills_desejadas.length > 0" class="mt-2 flex flex-wrap gap-2">
                <span
                  v-for="(skill, index) in form.skills_desejadas"
                  :key="index"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                >
                  {{ skill }}
                  <button
                    type="button"
                    @click="removeSkill(index)"
                    class="ml-1 text-blue-600 hover:text-blue-500"
                  >
                    ×
                  </button>
                </span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Localização</label>
              <input
                v-model="form.localizacao"
                type="text"
                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="Ex: Lisboa, Porto, Remoto, Híbrido"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="flex items-center">
                <input
                  v-model="form.ativa"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label class="ml-2 block text-sm text-gray-900">
                  Oportunidade ativa
                </label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.publica"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label class="ml-2 block text-sm text-gray-900">
                  Oportunidade pública
                </label>
              </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeModal"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="formLoading"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium disabled:opacity-50"
              >
                {{ formLoading ? 'A guardar...' : (showCreateModal ? 'Criar' : 'Atualizar') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Candidates Modal -->
    <div v-if="showCandidatesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              Candidatos - {{ selectedOportunidade?.titulo }}
            </h3>
            <button
              @click="showCandidatesModal = false"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div v-if="candidatesLoading" class="text-center py-8">
            <div class="animate-spin mx-auto h-8 w-8 text-blue-600">
              <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
            <p class="mt-2 text-gray-500">A carregar candidatos...</p>
          </div>

          <div v-else-if="candidates.length > 0" class="space-y-4 max-h-96 overflow-y-auto">
            <div
              v-for="candidate in candidates"
              :key="candidate.id"
              class="border border-gray-200 rounded-lg p-4"
            >
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <h4 class="text-lg font-medium text-gray-900">{{ candidate.nome }}</h4>
                  <p class="text-sm text-gray-600">{{ candidate.email }}</p>
                  <div class="mt-2 flex flex-wrap gap-2">
                    <span
                      v-for="skill in candidate.skills"
                      :key="skill"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                    >
                      {{ skill }}
                    </span>
                  </div>
                </div>
                <div class="flex items-center space-x-3">
                  <span
                    :class="[
                      'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                      candidate.score >= 80 ? 'bg-green-100 text-green-800' :
                      candidate.score >= 60 ? 'bg-yellow-100 text-yellow-800' :
                      'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ candidate.score }}% compatibilidade
                  </span>
                  <a
                    :href="`/storage/${candidate.cv_path}`"
                    target="_blank"
                    class="text-blue-600 hover:text-blue-500 text-sm font-medium"
                  >
                    Ver CV
                  </a>
                </div>
              </div>
              <p class="mt-2 text-xs text-gray-500">
                Candidatura em {{ formatDate(candidate.created_at) }}
              </p>
            </div>
          </div>

          <div v-else class="text-center py-8">
            <p class="text-gray-500">Ainda não há candidatos para esta oportunidade.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { companyBackofficeService, type Oportunidade, type Candidatura } from '@/services/companyBackoffice'

const router = useRouter()
const loading = ref(false)
const formLoading = ref(false)
const candidatesLoading = ref(false)
const oportunidades = ref<Oportunidade[]>([])
const candidates = ref<Candidatura[]>([])
const selectedOportunidade = ref<Oportunidade>()

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showCandidatesModal = ref(false)

const newSkill = ref('')
const form = reactive({
  titulo: '',
  descricao: '',
  skills_desejadas: [] as string[],
  localizacao: '',
  ativa: true,
  publica: true
})

onMounted(() => {
  loadOportunidades()
})

const loadOportunidades = async () => {
  loading.value = true
  try {
    oportunidades.value = await companyBackofficeService.getOportunidades()
  } catch (error) {
    console.error('Error loading oportunidades:', error)
  } finally {
    loading.value = false
  }
}

const addSkill = () => {
  if (newSkill.value.trim() && !form.skills_desejadas.includes(newSkill.value.trim())) {
    form.skills_desejadas.push(newSkill.value.trim())
    newSkill.value = ''
  }
}

const removeSkill = (index: number) => {
  form.skills_desejadas.splice(index, 1)
}

const resetForm = () => {
  form.titulo = ''
  form.descricao = ''
  form.skills_desejadas = []
  form.localizacao = ''
  form.ativa = true
  form.publica = true
  newSkill.value = ''
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedOportunidade.value = undefined
  resetForm()
}

const editOportunidade = (oportunidade: Oportunidade) => {
  selectedOportunidade.value = oportunidade
  form.titulo = oportunidade.titulo
  form.descricao = oportunidade.descricao
  form.skills_desejadas = [...oportunidade.skills_desejadas]
  form.ativa = oportunidade.ativa
  form.publica = oportunidade.publica
  showEditModal.value = true
}

const submitForm = async () => {
  if (form.skills_desejadas.length === 0) {
    alert('Por favor, adicione pelo menos uma skill desejada.')
    return
  }

  formLoading.value = true
  try {
    if (showCreateModal.value) {
      await companyBackofficeService.createOportunidade(form)
    } else if (selectedOportunidade.value) {
      await companyBackofficeService.updateOportunidade(selectedOportunidade.value.id, form)
    }
    
    closeModal()
    await loadOportunidades()
  } catch (error) {
    console.error('Error saving oportunidade:', error)
    alert('Erro ao guardar oportunidade')
  } finally {
    formLoading.value = false
  }
}

const deleteOportunidade = async (oportunidade: Oportunidade) => {
  if (confirm(`Tem certeza que deseja eliminar a oportunidade "${oportunidade.titulo}"?`)) {
    try {
      await companyBackofficeService.deleteOportunidade(oportunidade.id)
      await loadOportunidades()
    } catch (error) {
      console.error('Error deleting oportunidade:', error)
      alert('Erro ao eliminar oportunidade')
    }
  }
}

const viewCandidates = (oportunidade: Oportunidade) => {
  router.push({ 
    name: 'company.candidates', 
    params: { id: oportunidade.id } 
  })
}

const viewCandidatosPotencial = (oportunidade: Oportunidade) => {
  router.push({ 
    name: 'company.candidatosPotencial', 
    params: { id: oportunidade.id } 
  })
}

const viewReports = (oportunidade: Oportunidade) => {
  router.push({ 
    name: 'company.reports', 
    params: { id: oportunidade.id } 
  })
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-PT')
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
