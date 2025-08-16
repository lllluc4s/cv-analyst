<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <h1 class="text-2xl font-bold text-gray-900">Área do Colaborador</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">Olá, {{ profile?.candidato.nome }}</span>
            <button 
              @click="logout"
              class="text-sm text-red-600 hover:text-red-800"
            >
              Sair
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-8">
          <router-link 
            to="/colaborador/dashboard" 
            class="px-3 py-2 text-sm font-medium"
            :class="$route.name === 'colaborador.dashboard' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900'"
          >
            Dashboard
          </router-link>
          <router-link 
            to="/colaborador/contratos" 
            class="px-3 py-2 text-sm font-medium"
            :class="$route.name === 'colaborador.contratos' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900'"
          >
            Meus Contratos
          </router-link>
          <router-link 
            to="/colaborador/dias-nao-trabalhados" 
            class="px-3 py-2 text-sm font-medium"
            :class="$route.name === 'colaborador.dias-nao-trabalhados' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900'"
          >
            Dias Não Trabalhados
          </router-link>
          <router-link 
            to="/colaborador/mensagens" 
            class="px-3 py-2 text-sm font-medium"
            :class="$route.name === 'colaborador.mensagens' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900'"
          >
            Mensagens
          </router-link>
          <router-link 
            to="/colaborador/perfil" 
            class="px-3 py-2 text-sm font-medium"
            :class="$route.name === 'colaborador.perfil' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900'"
          >
            Perfil
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <router-view />
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import colaboradorService, { type ColaboradorProfile } from '@/services/colaboradorService'

const router = useRouter()
const profile = ref<ColaboradorProfile | null>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    // Verificar se tem acesso
    const acesso = await colaboradorService.verificarAcesso()
    if (!acesso.is_colaborador) {
      router.push('/candidatos/login')
      return
    }

    // Carregar perfil
    profile.value = await colaboradorService.getProfile()
  } catch (error) {
    console.error('Erro ao carregar dados:', error)
    router.push('/candidatos/login')
  } finally {
    loading.value = false
  }
})

const logout = () => {
  localStorage.removeItem('candidato_token')
  localStorage.removeItem('candidato_user')
  router.push('/candidatos/login')
}
</script>
