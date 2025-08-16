<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Candidaturas</h1>
      <p v-if="oportunidade" class="mt-2 text-gray-600">
        Para a vaga: <span class="font-semibold">{{ oportunidade.titulo }}</span>
      </p>
    </div>

    <!-- Filtros -->
    <div class="mb-6">
      <label for="oportunidade-select" class="block text-sm font-medium text-gray-700 mb-2">
        Filtrar por Oportunidade:
      </label>
      <select 
        id="oportunidade-select"
        v-model="selectedOportunidadeId" 
        @change="loadCandidaturas"
        class="block w-full max-w-sm px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
      >
        <option value="">Todas as oportunidades</option>
        <option v-for="op in oportunidades" :key="op.id" :value="op.id">
          {{ op.titulo }}
        </option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Erro</h3>
          <div class="mt-2 text-sm text-red-700">{{ error }}</div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!candidaturas.length" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma candidatura encontrada</h3>
      <p class="mt-1 text-sm text-gray-500">Ainda não há candidaturas para esta oportunidade.</p>
    </div>

    <!-- Candidaturas List -->
    <div v-else class="space-y-4">
      <div 
        v-for="candidatura in candidaturas" 
        :key="candidatura.id"
        class="bg-white shadow rounded-lg p-6 border border-gray-200"
      >
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <!-- Candidato Info -->
            <div class="flex items-center gap-4 mb-4">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">
                  {{ candidatura.nome }} ({{ candidatura.apelido }})
                </h3>
                <p class="text-sm text-gray-600">{{ candidatura.email }}</p>
                <p class="text-sm text-gray-600">{{ candidatura.telefone }}</p>
              </div>
            </div>

            <!-- Skills -->
            <div class="mb-4">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Match de skills:</h4>
              <div class="flex flex-wrap gap-2">
                <span 
                  v-for="skill in candidatura.skills_extraidas" 
                  :key="skill"
                  :class="[
                    'px-2 py-1 rounded-full text-xs font-medium',
                    isSkillMatched(skill, candidatura) 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ skill }}
                  <span v-if="isSkillMatched(skill, candidatura)" class="ml-1">✓</span>
                </span>
              </div>
              <p v-if="!candidatura.skills_extraidas?.length" class="text-sm text-gray-500 italic">
                Nenhuma skill extraída ainda
              </p>
            </div>

            <!-- Info da oportunidade e data -->
            <p v-if="!selectedOportunidadeId" class="text-xs font-medium text-blue-600 mb-1">
              Vaga aplicada: {{ candidatura.oportunidade?.titulo || 'Oportunidade não encontrada' }}
            </p>
            <p class="text-xs text-gray-500">
              Candidatura enviada em {{ candidatura.created_at ? formatDate(candidatura.created_at) : 'Data não disponível' }}
            </p>
          </div>

          <!-- Pontuação e Actions -->
          <div class="flex flex-col items-end gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">{{ candidatura.pontuacao_skills }}</div>
              <div class="text-xs text-gray-500">Skills Match</div>
            </div>
            
            <div class="flex flex-col gap-2">
              <a 
                :href="storageUrl(candidatura.cv_path)" 
                target="_blank"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Ver CV
              </a>
              
              <button 
                @click="removerCandidatura(candidatura)"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Remover candidatura
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para editar skills -->
  <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <h3 class="text-lg font-medium text-gray-900 text-center mb-4">
          Editar Skills - {{ candidaturaEditando?.nome }}
        </h3>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Skills (uma por linha):
          </label>
          <textarea
            v-model="skillsEditText"
            rows="6"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            placeholder="Digite as skills, uma por linha..."
          ></textarea>
        </div>
        
        <div class="flex gap-3">
          <button
            @click="salvarSkills"
            :disabled="salvandoSkills"
            class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <div v-if="salvandoSkills" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
            {{ salvandoSkills ? 'Salvando...' : 'Salvar' }}
          </button>
          <button
            @click="fecharModal"
            class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { candidaturasService, oportunidadesService, type Candidatura, type Oportunidade } from '../../services/api'
import { storageUrl } from '@/utils/urlBuilder'

const candidaturas = ref<Candidatura[]>([])
const oportunidades = ref<Oportunidade[]>([])
const oportunidade = ref<Oportunidade | null>(null)
const selectedOportunidadeId = ref('')
const loading = ref(true)
const error = ref('')

// Modal de edição
const showEditModal = ref(false)
const candidaturaEditando = ref<Candidatura | null>(null)
const skillsEditText = ref('')
const salvandoSkills = ref(false)

const loadOportunidades = async () => {
  try {
    const response = await oportunidadesService.getAll(1)
    oportunidades.value = response.data
  } catch (err) {
    console.error('Erro ao carregar oportunidades:', err)
  }
}

const loadCandidaturas = async () => {
  try {
    loading.value = true
    error.value = ''
    
    if (selectedOportunidadeId.value) {
      const response = await candidaturasService.getCandidaturasPorOportunidade(Number(selectedOportunidadeId.value))
      candidaturas.value = response.candidaturas
      oportunidade.value = response.oportunidade
    } else {
      const response = await candidaturasService.getAll()
      candidaturas.value = response.data
      oportunidade.value = null
    }
  } catch (err) {
    error.value = 'Erro ao carregar candidaturas. Verifique se o backend está rodando.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const isSkillMatched = (skill: string, candidatura: Candidatura) => {
  if (!candidatura.oportunidade?.skills_desejadas) return false
  return candidatura.oportunidade.skills_desejadas.some(
    desejada => (typeof desejada === 'string' ? desejada : desejada.nome).toLowerCase() === skill.toLowerCase()
  )
}

const editarSkills = (candidatura: Candidatura) => {
  candidaturaEditando.value = candidatura
  skillsEditText.value = candidatura.skills_extraidas?.join('\n') || ''
  showEditModal.value = true
}

const salvarSkills = async () => {
  if (!candidaturaEditando.value) return
  
  try {
    salvandoSkills.value = true
    const skills = skillsEditText.value
      .split('\n')
      .map(s => s.trim())
      .filter(s => s.length > 0)
    
    await candidaturasService.atualizarSkills(candidaturaEditando.value.id!, skills)
    await loadCandidaturas()
    fecharModal()
  } catch (err) {
    error.value = 'Erro ao salvar skills'
    console.error(err)
  } finally {
    salvandoSkills.value = false
  }
}

const removerCandidatura = async (candidatura: Candidatura) => {
  if (!confirm('Tem certeza que deseja remover esta candidatura?')) return
  try {
    loading.value = true
    await candidaturasService.delete(candidatura.slug!)
    await loadCandidaturas()
  } catch (err) {
    error.value = 'Erro ao remover candidatura.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const fecharModal = () => {
  showEditModal.value = false
  candidaturaEditando.value = null
  skillsEditText.value = ''
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(async () => {
  await loadOportunidades()
  await loadCandidaturas()
})
</script>
