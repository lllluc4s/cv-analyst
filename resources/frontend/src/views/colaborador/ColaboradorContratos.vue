<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Meus Contratos</h2>
        
        <!-- Loading state -->
        <div v-if="loading" class="text-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-2 text-sm text-gray-600">Carregando contratos...</p>
        </div>

        <!-- Contratos -->
        <div v-else-if="contratos" class="space-y-4">
          <div v-if="contratos.length === 0" class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">📄</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum contrato encontrado</h3>
            <p class="text-gray-600">Você ainda não foi contratado por nenhuma empresa.</p>
          </div>

          <div v-else class="grid gap-6">
            <div 
              v-for="contrato in contratos" 
              :key="contrato.id"
              class="bg-white border rounded-lg shadow-sm hover:shadow-md transition-shadow"
            >
              <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                  <div class="flex items-center space-x-3">
                    <div 
                      v-if="contrato.empresa.logo_url" 
                      class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center"
                    >
                      <img :src="contrato.empresa.logo_url" :alt="contrato.empresa.nome" class="w-8 h-8 object-contain">
                    </div>
                    <div 
                      v-else 
                      class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center"
                    >
                      <span class="text-lg font-semibold text-gray-600">
                        {{ contrato.empresa.nome.charAt(0) }}
                      </span>
                    </div>
                    <div>
                      <h3 class="text-lg font-medium text-gray-900">{{ contrato.posicao }}</h3>
                      <p class="text-sm text-gray-600">{{ contrato.empresa.nome }}</p>
                    </div>
                  </div>
                  <div class="text-right">
                    <span 
                      class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                      :class="getStatusClass(contrato)"
                    >
                      {{ getStatusText(contrato) }}
                    </span>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                  <div>
                    <p class="font-medium text-gray-900">Departamento</p>
                    <p class="text-gray-600">{{ contrato.departamento }}</p>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Vencimento</p>
                    <p class="text-gray-600">{{ formatCurrency(contrato.vencimento) }}</p>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Início do Contrato</p>
                    <p class="text-gray-600">{{ formatDate(contrato.data_inicio_contrato) }}</p>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Fim do Contrato</p>
                    <p class="text-gray-600">
                      {{ contrato.data_fim_contrato ? formatDate(contrato.data_fim_contrato) : 'Sem termo' }}
                    </p>
                  </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                  <div class="flex justify-between items-center">
                    <div>
                      <p class="text-sm font-medium text-gray-900">Oportunidade de Origem</p>
                      <p class="text-sm text-gray-600">{{ contrato.oportunidade_origem.titulo }}</p>
                    </div>
                    <div class="text-right">
                      <p class="text-sm font-medium text-gray-900">Candidatura</p>
                      <p class="text-sm text-gray-600">
                        {{ contrato.candidatura_data ? formatDate(contrato.candidatura_data) : 'N/A' }}
                      </p>
                    </div>
                  </div>
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
import colaboradorService, { type ColaboradorData } from '@/services/colaboradorService'

const contratos = ref<ColaboradorData[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    contratos.value = await colaboradorService.getHistoricoContratos()
  } catch (error) {
    console.error('Erro ao carregar contratos:', error)
  } finally {
    loading.value = false
  }
})

const formatCurrency = (value: number | null) => {
  return colaboradorService.formatCurrency(value)
}

const formatDate = (date: string) => {
  return colaboradorService.formatDate(date)
}

const getStatusClass = (contrato: ColaboradorData) => {
  if (!contrato.contrato_ativo) {
    return 'bg-red-100 text-red-800'
  }
  
  if (contrato.sem_termo) {
    return 'bg-green-100 text-green-800'
  }
  
  return 'bg-blue-100 text-blue-800'
}

const getStatusText = (contrato: ColaboradorData) => {
  if (!contrato.contrato_ativo) {
    return 'Inativo'
  }
  
  if (contrato.sem_termo) {
    return 'Ativo - Sem termo'
  }
  
  return 'Ativo - Prazo determinado'
}
</script>
