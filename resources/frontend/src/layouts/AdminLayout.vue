<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <router-link to="/admin" class="flex-shrink-0">
              <h1 class="text-xl font-bold text-blue-600">CV Analyst</h1>
            </router-link>
            
            <!-- Navigation Menu -->
            <div class="hidden md:ml-10 md:flex md:space-x-8">
              <router-link 
                to="/admin"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                :class="{ 'border-blue-500 text-blue-600': $route.name === 'oportunidades.index' }"
              >
                Admin
              </router-link>
              
              <router-link 
                to="/reports"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                :class="{ 'border-blue-500 text-blue-600': $route.name === 'reports' }"
              >
                Análises
              </router-link>
              
              <router-link 
                to="/admin/oportunidades"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                :class="{ 'border-blue-500 text-blue-600': $route.path.includes('/admin/oportunidades') }"
              >
                Oportunidades
              </router-link>
              
              <router-link 
                to="/candidaturas"
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                :class="{ 'border-blue-500 text-blue-600': $route.name === 'candidaturas.index' }"
              >
                Candidaturas
              </router-link>
            </div>
          </div>
          
          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-700">{{ company?.name }}</span>
            <button 
              @click="handleLogout"
              class="text-gray-500 hover:text-gray-700 text-sm font-medium"
            >
              Sair
            </button>
          </div>
        </div>
      </div>
    </nav>
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <router-view />
    </main>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../services/auth'

const router = useRouter()
const { company, logout, checkAuth } = useAuth()

const handleLogout = async () => {
  await logout()
  router.push('/login')
}

onMounted(async () => {
  await checkAuth()
})
</script>
