<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { companyAuthService } from '@/services/companyAuth'
import { candidatoAuthService } from '@/services/candidatoAuth'
import CvAnalyzer from './components/CvAnalyzer.vue'

const router = useRouter()
const route = useRoute()

// Company auth state
const isAuthenticated = ref(false)
const companyName = ref('')
const showProfileMenu = ref(false)

// Candidato auth state  
const isCandidatoAuthenticated = ref(false)
const candidatoName = ref('')
const showCandidatoProfileMenu = ref(false)

onMounted(() => {
  checkAuthStatus()
})

// Verificar autenticação quando a rota muda
watch(() => route.path, () => {
  checkAuthStatus()
})

const checkAuthStatus = async () => {
  // Check company auth
  if (companyAuthService.isAuthenticated()) {
    try {
      const company = await companyAuthService.me()
      isAuthenticated.value = true
      companyName.value = company.name
    } catch (error) {
      isAuthenticated.value = false
      companyName.value = ''
    }
  } else {
    isAuthenticated.value = false
    companyName.value = ''
  }

  // Check candidato auth
  if (candidatoAuthService.isAuthenticated()) {
    try {
      const candidato = await candidatoAuthService.me()
      isCandidatoAuthenticated.value = true
      candidatoName.value = `${candidato.nome} ${candidato.apelido}`
    } catch (error) {
      isCandidatoAuthenticated.value = false
      candidatoName.value = ''
    }
  } else {
    isCandidatoAuthenticated.value = false
    candidatoName.value = ''
  }
}

const logout = async () => {
  await companyAuthService.logout()
  isAuthenticated.value = false
  companyName.value = ''
  showProfileMenu.value = false
  router.push('/empresas/login')
}

const logoutCandidato = async () => {
  await candidatoAuthService.logout()
  isCandidatoAuthenticated.value = false
  candidatoName.value = ''
  showCandidatoProfileMenu.value = false
  router.push('/candidatos/login')
}

// Fechar menu do perfil quando clicar fora
const closeProfileMenu = (event: Event) => {
  const target = event.target as HTMLElement
  if (!target.closest('.profile-menu-container')) {
    showProfileMenu.value = false
    showCandidatoProfileMenu.value = false
  }
}

onMounted(() => {
  checkAuthStatus()
  document.addEventListener('click', closeProfileMenu)
})

// Cleanup
onUnmounted(() => {
  document.removeEventListener('click', closeProfileMenu)
})
</script>

<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <router-link to="/" class="text-2xl font-bold text-blue-600">
              CV Analyst
            </router-link>
          </div>
          <div class="flex items-center space-x-4">
            <!-- Links de navegação públicos -->
            <router-link 
              to="/" 
              class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
              :class="{ 'text-blue-600 bg-blue-50': $route.name === 'home' }"
            >
              Início
            </router-link>
            <router-link 
              to="/cv-analysis" 
              class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
              :class="{ 'text-blue-600 bg-blue-50': $route.name === 'cv.analysis' }"
            >
              Análises
            </router-link>
            <router-link 
              to="/oportunidades" 
              class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
              :class="{ 'text-blue-600 bg-blue-50': $route.name === 'oportunidades.public.all' }"
            >
              Oportunidades
            </router-link>
            <router-link 
              to="/empresas" 
              class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
              :class="{ 'text-blue-600 bg-blue-50': $route.name === 'empresas.public' }"
            >
              Empresas
            </router-link>
            
            <!-- Menu de perfil para candidatos autenticados -->
            <template v-if="isCandidatoAuthenticated">
              <div class="relative profile-menu-container">
                <div>
                  <button 
                    @click="showCandidatoProfileMenu = !showCandidatoProfileMenu"
                    class="flex items-center text-sm rounded-full text-gray-700 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 px-3 py-2"
                  >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ candidatoName }}
                    <svg class="ml-2 -mr-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                </div>

                <div 
                  v-if="showCandidatoProfileMenu"
                  class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                  @click="showCandidatoProfileMenu = false"
                >
                  <div class="py-1">
                    <router-link
                      to="/candidatos/profile"
                      class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                      :class="{ 'bg-gray-100 text-gray-900': $route.name === 'candidatos.profile' }"
                    >
                      <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Editar Perfil
                    </router-link>
                    <router-link
                      to="/candidatos/candidaturas"
                      class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                      :class="{ 'bg-gray-100 text-gray-900': $route.name === 'candidatos.candidaturas' }"
                    >
                      <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      Minhas Candidaturas
                    </router-link>
                    <router-link
                      to="/candidatos/cv-optimizer"
                      class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                      :class="{ 'bg-gray-100 text-gray-900': $route.name === 'candidatos.cv-optimizer' }"
                    >
                      <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                      </svg>
                      🤖 IA
                    </router-link>
                    <div class="border-t border-gray-100"></div>
                    <button
                      @click="logoutCandidato"
                      class="group flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                    >
                      <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                      Sair
                    </button>
                  </div>
                </div>
              </div>
            </template>
            
            <!-- Links administrativos (quando autenticado como empresa) -->
            <template v-else-if="isAuthenticated">
              <router-link 
                to="/admin/oportunidades" 
                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                :class="{ 'text-blue-600 bg-blue-50': $route.name?.toString().includes('oportunidades.') && $route.name !== 'oportunidades.public.all' }"
              >
                Oportunidades Internas
              </router-link>

              
              <!-- Dropdown do Perfil da Empresa -->
              <div class="relative inline-block text-left profile-menu-container">
                <div>
                  <button
                    @click="showProfileMenu = !showProfileMenu"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                  >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ companyName }}
                    <svg class="ml-2 -mr-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                </div>

                <div 
                  v-if="showProfileMenu"
                  class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                  @click="showProfileMenu = false"
                >
                  <div class="py-1">
                    <router-link
                      to="/empresas/perfil"
                      class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                      :class="{ 'bg-gray-100 text-gray-900': $route.name === 'company.profile' }"
                    >
                      <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Editar Perfil
                    </router-link>
                    <div class="border-t border-gray-100"></div>
                    <button
                      @click="logout"
                      class="group flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                    >
                      <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                      Sair
                    </button>
                  </div>
                </div>
              </div>
            </template>
            
            <!-- Botões de login quando não autenticado -->
            <template v-else>
              <div class="flex items-center space-x-2">
                <router-link 
                  to="/candidatos/login" 
                  class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm font-medium"
                >
                  Login Candidato
                </router-link>
                <router-link 
                  to="/empresas/login" 
                  class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium"
                >
                  Login Empresa
                </router-link>
              </div>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1">
      <router-view />
    </main>
  </div>
</template>

<style>
.router-link-active {
  color: #2563eb;
}
</style>
