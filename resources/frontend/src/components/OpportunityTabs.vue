<template>
  <div class="border-b border-gray-200 mb-6">
    <nav class="-mb-px flex space-x-8">
      <router-link
        :to="`/empresas/oportunidades/${oportunidadeId}/candidatos`"
        :class="[
          'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
          isActive('candidates')
            ? 'border-blue-500 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
        ]"
      >
        📋 Candidaturas
      </router-link>
      <router-link
        :to="`/empresas/oportunidades/${oportunidadeId}/kanban`"
        :class="[
          'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
          isActive('kanban')
            ? 'border-blue-500 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
        ]"
      >
        🎯 Board Kanban
      </router-link>
      <router-link
        :to="`/empresas/oportunidades/${oportunidadeId}/colaboradores`"
        :class="[
          'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
          isActive('colaboradores')
            ? 'border-blue-500 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
        ]"
      >
        👥 Colaboradores
      </router-link>
      <router-link
        :to="`/empresas/oportunidades/${oportunidadeId}/relatorios`"
        :class="[
          'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
          isActive('relatorios')
            ? 'border-blue-500 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
        ]"
      >
        📊 Relatórios
      </router-link>
      <router-link
        :to="`/empresas/oportunidades/${oportunidadeId}/feedbacks`"
        :class="[
          'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
          isActive('feedbacks')
            ? 'border-blue-500 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
        ]"
      >
        💬 Feedbacks
      </router-link>
      <router-link
        :to="`/empresas/oportunidades/${oportunidadeId}/mensagens`"
        :class="[
          'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
          isActive('mensagens')
            ? 'border-blue-500 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
        ]"
      >
        📨 Mensagens
      </router-link>
      <router-link
        :to="`/empresas/candidatos-online-map`"
        :class="[
          'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
          isActive('candidatos-online-map')
            ? 'border-blue-500 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
        ]"
      >
        🌍 Candidatos Online
      </router-link>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'

interface Props {
  oportunidadeId: string | number
}

const props = defineProps<Props>()
const route = useRoute()

const isActive = (section: string) => {
  const routeName = route.name as string
  const path = route.path
  
  // Verificar pelo nome da rota ou pelo caminho
  if (routeName?.includes(section)) {
    return true
  }
  
  // Verificar pelo caminho da URL
  if (section === 'candidates' && path.includes('/candidatos')) {
    return true
  }
  if (section === 'kanban' && path.includes('/kanban')) {
    return true
  }
  if (section === 'colaboradores' && path.includes('/colaboradores')) {
    return true
  }
  if (section === 'relatorios' && path.includes('/relatorios')) {
    return true
  }
  if (section === 'feedbacks' && path.includes('/feedbacks')) {
    return true
  }
  if (section === 'mensagens' && path.includes('/mensagens')) {
    return true
  }
  if (section === 'candidatos-online-map' && path.includes('/candidatos-online-map')) {
    return true
  }
  
  return false
}
</script>
