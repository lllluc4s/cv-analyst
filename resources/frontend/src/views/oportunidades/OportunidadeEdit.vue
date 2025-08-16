<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { oportunidadesService, type Oportunidade } from '../../services/api'

interface Props {
  slug: string
}

const props = defineProps<Props>()
const router = useRouter()

const form = ref({
  titulo: '',
  descricao: '',
  skills_desejadas: [] as { nome: string; peso?: number }[],
  localizacao: '',
  ativa: true,
  publica: false
})

const originalOportunidade = ref<Oportunidade | null>(null)
const newSkill = ref('')
const newSkillPeso = ref(0)
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const errors = ref<Record<string, string[]>>({})

const loadOportunidade = async () => {
  try {
    loading.value = true
    error.value = ''
    const oportunidade = await oportunidadesService.getBySlug(props.slug)
    originalOportunidade.value = oportunidade
    
    // Preencher o formulário
    form.value = {
      titulo: oportunidade.titulo,
      descricao: oportunidade.descricao,
      skills_desejadas: [...oportunidade.skills_desejadas],
      localizacao: oportunidade.localizacao || '',
      ativa: oportunidade.ativa ?? true,
      publica: oportunidade.publica ?? false
    }
  } catch (err) {
    error.value = 'Erro ao carregar oportunidade. Verifique se o backend está rodando.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const addSkill = () => {
  const skill = newSkill.value.trim()
  let peso = Number(newSkillPeso.value)
  if (!peso || peso < 1) peso = 1
  if (skill && !form.value.skills_desejadas.some(s => s.nome === skill)) {
    form.value.skills_desejadas.push(peso ? { nome: skill, peso } : { nome: skill })
    newSkill.value = ''
    newSkillPeso.value = 0
  }
}

const removeSkill = (index: number) => {
  form.value.skills_desejadas.splice(index, 1)
}

const handleSubmit = async () => {
  try {
    saving.value = true
    error.value = ''
    errors.value = {}

    if (form.value.skills_desejadas.length === 0) {
      errors.value.skills_desejadas = ['Pelo menos uma skill é obrigatória']
      return
    }

    await oportunidadesService.update(props.slug, form.value)
    router.push('/admin/oportunidades')
  } catch (err: any) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors
    } else {
      error.value = 'Erro ao atualizar oportunidade. Verifique se o backend está rodando.'
    }
    console.error(err)
  } finally {
    saving.value = false
  }
}

const handleKeyPress = (e: KeyboardEvent) => {
  if (e.key === 'Enter') {
    e.preventDefault()
    addSkill()
  }
}

onMounted(() => {
  loadOportunidade()
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Editar Oportunidade</h1>
      <p class="text-gray-600 mt-1">Atualize as informações da vaga</p>
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
      <div class="mt-4">
        <router-link
          to="/admin/oportunidades"
          class="text-blue-600 hover:text-blue-800 font-medium"
        >
          ← Voltar para Oportunidades
        </router-link>
      </div>
    </div>

    <!-- Form -->
    <div v-else class="bg-white rounded-lg shadow-lg p-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Título -->
        <div>
          <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
            Título *
          </label>
          <input
            id="titulo"
            v-model="form.titulo"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            :class="{ 'border-red-500': errors.titulo }"
            placeholder="Ex: Desenvolvedor Frontend Sênior"
          />
          <div v-if="errors.titulo" class="mt-1 text-sm text-red-600">
            {{ errors.titulo[0] }}
          </div>
        </div>

        <!-- Descrição -->
        <div>
          <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
            Descrição *
          </label>
          <textarea
            id="descricao"
            v-model="form.descricao"
            required
            rows="6"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            :class="{ 'border-red-500': errors.descricao }"
            placeholder="Descreva a vaga, responsabilidades, requisitos..."
          ></textarea>
          <div v-if="errors.descricao" class="mt-1 text-sm text-red-600">
            {{ errors.descricao[0] }}
          </div>
        </div>

        <!-- Skills -->
        <div>
          <label for="skills" class="block text-sm font-medium text-gray-700 mb-2">
            Skills Desejadas *
          </label>
          
          <!-- Input para adicionar skills -->
          <div class="flex gap-2 mb-3">
            <input
              v-model="newSkill"
              @keypress="handleKeyPress"
              type="text"
              placeholder="Skill"
              class="w-1/2 px-2 py-1 border rounded"
            />
            <input
              v-model.number="newSkillPeso"
              type="number"
              min="0"
              max="100"
              placeholder="Peso (%)"
              class="w-1/4 px-2 py-1 border rounded"
            />
            <button
              type="button"
              @click="addSkill"
              class="bg-blue-500 text-white px-3 py-1 rounded"
            >
              Adicionar
            </button>
          </div>

          <!-- Lista de skills adicionadas -->
          <ul>
            <li v-for="(skill, idx) in form.skills_desejadas" :key="skill.nome" class="flex items-center gap-2 mb-1">
              <span>{{ skill.nome }} ({{ skill.peso }}%)</span>
              <button type="button" @click="removeSkill(idx)" class="text-red-500">Remover</button>
            </li>
          </ul>

          <p class="text-sm text-gray-500">
            Digite uma skill e pressione Enter ou clique em "Adicionar". Mínimo de 1 skill obrigatória.
          </p>
          
          <div v-if="errors.skills_desejadas" class="mt-1 text-sm text-red-600">
            {{ errors.skills_desejadas[0] }}
          </div>
        </div>

        <!-- Localização -->
        <div>
          <label for="localizacao" class="block text-sm font-medium text-gray-700 mb-2">
            Localização
          </label>
          <input
            id="localizacao"
            v-model="form.localizacao"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            :class="{ 'border-red-500': errors.localizacao }"
            placeholder="Ex: Lisboa, Porto, Remoto, Híbrido"
          />
          <p class="text-xs text-gray-500 mt-1">
            Indique a localização da vaga (cidade, país) ou modalidade (remoto, híbrido)
          </p>
          <div v-if="errors.localizacao" class="mt-1 text-sm text-red-600">
            {{ errors.localizacao[0] }}
          </div>
        </div>

        <!-- Ativa -->
        <div>
          <label class="flex items-center">
            <input
              v-model="form.ativa"
              type="checkbox"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-2 text-sm text-gray-700">Oportunidade ativa</span>
          </label>
        </div>

        <!-- Pública -->
        <div>
          <label class="flex items-center">
            <input
              v-model="form.publica"
              type="checkbox"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="ml-2 text-sm text-gray-700">Oportunidade pública</span>
          </label>
          <p class="text-xs text-gray-500 mt-1">
            Marque esta opção para que a oportunidade apareça na listagem pública
          </p>
        </div>

        <!-- Actions -->
        <div class="flex justify-between pt-6 border-t">
          <router-link
            to="/admin/oportunidades"
            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
          >
            Cancelar
          </router-link>
          <button
            type="submit"
            :disabled="saving"
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="saving">Salvando...</span>
            <span v-else>Salvar Alterações</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
