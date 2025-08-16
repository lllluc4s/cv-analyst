<template>
  <nav class="bg-white shadow mb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex">
          <div class="flex-shrink-0 flex items-center">
            <h1 class="text-xl font-bold text-gray-900">CV Analyst</h1>
          </div>
          <div class="ml-10 flex items-baseline space-x-4">
            <router-link 
              to="/candidatos/profile"
              class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
              :class="{ 'text-blue-600 bg-blue-50': $route.path === '/candidatos/profile' }"
            >
              Perfil
            </router-link>
            <router-link 
              to="/candidatos/candidaturas"
              class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
              :class="{ 'text-blue-600 bg-blue-50': $route.path === '/candidatos/candidaturas' }"
            >
              Candidaturas
            </router-link>
            <router-link 
              to="/candidatos/cv-optimizer"
              class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
              :class="{ 'text-blue-600 bg-blue-50': $route.path === '/candidatos/cv-optimizer' }"
            >
              CV Optimizer
            </router-link>
            <router-link 
              to="/candidatos/mensagens"
              class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
              :class="{ 'text-blue-600 bg-blue-50': $route.path === '/candidatos/mensagens' }"
            >
              💬 Mensagens
            </router-link>
          </div>
        </div>
        <div class="flex items-center">
          <button 
            @click="logout"
            class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
          >
            Sair
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { candidatoAuthService } from '@/services/candidatoAuth'
import { useRouter } from 'vue-router'

const router = useRouter()

const logout = async () => {
  await candidatoAuthService.logout()
  router.push('/candidatos/login')
}
</script>
