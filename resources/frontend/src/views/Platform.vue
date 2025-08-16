<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        CV Analyst - Plataforma
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Escolha como quer aceder à plataforma
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 space-y-6">
        
        <!-- Para Candidatos -->
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Candidatos</h3>
              <p class="text-sm text-gray-500">Análise de CV e candidaturas a oportunidades</p>
            </div>
          </div>
          
          <div class="space-y-3">
            <router-link
              to="/cv-analysis"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
            >
              Analisar CV
            </router-link>
            
            <router-link
              to="/admin/oportunidades"
              class="w-full flex justify-center py-2 px-4 border border-blue-600 rounded-md shadow-sm text-sm font-medium text-blue-600 bg-white hover:bg-blue-50"
            >
              Ver Oportunidades
            </router-link>
          </div>
        </div>

        <!-- Para Empresas -->
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h.01M7 3h.01"></path>
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Empresas</h3>
              <p class="text-sm text-gray-500">Gestão de oportunidades e candidatos</p>
            </div>
          </div>
          
          <div class="space-y-3">
            <router-link
              to="/empresas/login"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700"
            >
              Login de Empresa
            </router-link>
            
            <router-link
              to="/empresas/register"
              class="w-full flex justify-center py-2 px-4 border border-green-600 rounded-md shadow-sm text-sm font-medium text-green-600 bg-white hover:bg-green-50"
            >
              Registar Empresa
            </router-link>
          </div>
        </div>

        <!-- Admin (Existente) -->
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900">Administração</h3>
              <p class="text-sm text-gray-500">Gestão geral da plataforma</p>
            </div>
          </div>
          
          <div class="space-y-3">
            <router-link
              to="/admin/oportunidades"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700"
            >
              Painel Admin
            </router-link>
            
            <router-link
              to="/reports"
              class="w-full flex justify-center py-2 px-4 border border-purple-600 rounded-md shadow-sm text-sm font-medium text-purple-600 bg-white hover:bg-purple-50"
            >
              Relatórios
            </router-link>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Componente simples sem lógica adicional
</script>
