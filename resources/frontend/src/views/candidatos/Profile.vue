<template>
  <div>
    <CandidatoNav />
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
              Meu Perfil
            </h3>
            
            <div v-if="loading" class="text-center py-8">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <p class="mt-2 text-gray-600">Carregando perfil...</p>
            </div>
            
            <div v-else-if="error" class="text-center py-8">
              <p class="text-red-600">{{ error }}</p>
              <button @click="loadProfile" class="mt-2 btn btn-primary">
                Tentar novamente
              </button>
            </div>
            
            <form v-else @submit.prevent="updateProfile" class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                  <label for="nome" class="block text-sm font-medium text-gray-700">
                    Nome *
                  </label>
                  <input
                    type="text"
                    id="nome"
                    v-model="form.nome"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                
                <div>
                  <label for="apelido" class="block text-sm font-medium text-gray-700">
                    Apelido *
                  </label>
                  <input
                    type="text"
                    id="apelido"
                    v-model="form.apelido"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">
                    Email *
                  </label>
                  <input
                    type="email"
                    id="email"
                    v-model="form.email"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                
                <div>
                  <label for="telefone" class="block text-sm font-medium text-gray-700">
                    Telefone *
                  </label>
                  <input
                    type="tel"
                    id="telefone"
                    v-model="form.telefone"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                
                <div class="sm:col-span-2">
                  <label for="data_nascimento" class="block text-sm font-medium text-gray-700">
                    Data de Nascimento *
                  </label>
                  <input
                    type="date"
                    id="data_nascimento"
                    v-model="form.data_nascimento"
                    required
                    :max="new Date().toISOString().split('T')[0]"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
              </div>
              
              <!-- Profile Photo Section -->
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-4">
                  Foto de Perfil
                </label>
                
                <div class="flex items-center space-x-6">
                  <!-- Current Photo Display -->
                  <div class="flex-shrink-0">
                    <img 
                      :src="currentProfilePhoto" 
                      :alt="form.nome || 'Foto de perfil'"
                      class="h-20 w-20 rounded-full object-cover border-2 border-gray-300"
                      @error="handlePhotoError"
                    />
                  </div>
                  
                  <!-- Upload Controls -->
                  <div class="flex-1">
                    <div class="space-y-2">
                      <input
                        type="file"
                        id="profile_photo"
                        ref="profilePhotoInput"
                        @change="handleProfilePhotoChange"
                        accept="image/jpeg,image/png,image/jpg,image/gif"
                        class="hidden"
                      />
                      
                      <div class="flex space-x-2">
                        <button
                          type="button"
                          @click="triggerProfilePhotoUpload"
                          :disabled="uploading"
                          class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                          </svg>
                          {{ uploading ? 'Enviando...' : (candidato?.profile_photo ? 'Alterar foto' : 'Adicionar foto') }}
                        </button>
                        
                        <button
                          v-if="candidato?.profile_photo"
                          type="button"
                          @click="removeProfilePhoto"
                          :disabled="uploading"
                          class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
                        >
                          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                          </svg>
                          Remover foto
                        </button>
                      </div>
                      
                      <p class="text-xs text-gray-500">
                        Formatos aceitos: JPEG, PNG, JPG, GIF. Tamanho máximo: 2MB
                      </p>
                      
                      <div v-if="photoError" class="text-red-600 text-sm">
                        {{ photoError }}
                      </div>
                      
                      <div v-if="photoSuccess" class="text-green-600 text-sm">
                        {{ photoSuccess }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div>
                <label for="linkedin_url" class="block text-sm font-medium text-gray-700">
                  LinkedIn
                </label>
                <input
                  type="url"
                  id="linkedin_url"
                  v-model="form.linkedin_url"
                  placeholder="https://linkedin.com/in/seu-perfil"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  CV Atual
                </label>
                <div v-if="candidato?.cv_path" class="mb-4">
                  <p class="text-sm text-gray-600 mb-2">CV atual: {{ candidato.cv_path.split('/').pop() }}</p>
                  <a :href="`${apiBaseUrl}/storage/${candidato.cv_path}`" target="_blank" 
                     class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Ver CV atual
                  </a>
                </div>
                
                <div>
                  <label for="cv" class="block text-sm font-medium text-gray-700 mb-2">
                    Atualizar CV
                  </label>
                  <input
                    type="file"
                    id="cv"
                    @change="handleFileChange"
                    accept=".pdf,.doc,.docx"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                  />
                  <p class="mt-1 text-xs text-gray-500">
                    Formatos aceitos: PDF, DOC, DOCX. Tamanho máximo: 10MB
                  </p>
                </div>
              </div>
              
              <div v-if="updateError" class="text-red-600 text-sm">
                {{ updateError }}
              </div>
              
              <div v-if="updateSuccess" class="text-green-600 text-sm">
                {{ updateSuccess }}
              </div>
              
              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="updating"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                >
                  <span v-if="updating" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                  {{ updating ? 'Atualizando...' : 'Atualizar Perfil' }}
                </button>
              </div>
            </form>
          </div>
        </div>
        
        <!-- Ações Rápidas -->
        <div class="mt-6 bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Ações Rápidas
            </h3>
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <!-- Botão para área de colaborador -->
              <div v-if="isColaborador" class="text-center">
                <button
                  @click="goToColaboradorArea"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                  Área do Colaborador
                </button>
                <p class="mt-2 text-sm text-gray-500">Acesse sua área de colaborador</p>
              </div>
              
              <!-- Botão para candidaturas -->
              <div class="text-center">
                <router-link
                  to="/candidatos/candidaturas"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  Minhas Candidaturas
                </router-link>
                <p class="mt-2 text-sm text-gray-500">Veja suas candidaturas</p>
              </div>
              
              <!-- Botão para CV Optimizer -->
              <div class="text-center">
                <router-link
                  to="/candidatos/cv-optimizer"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  Otimizar CV
                </router-link>
                <p class="mt-2 text-sm text-gray-500">Otimize seu CV com IA</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Seção de Privacidade e Dados -->
        <div class="bg-white shadow rounded-lg mt-6">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
              Privacidade e Dados
            </h3>
            
            <div class="space-y-6">
              <!-- Controle de Visibilidade -->
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </div>
                  <div class="ml-3 flex-1">
                    <h4 class="text-sm font-medium text-blue-900">
                      Visibilidade do Perfil
                    </h4>
                    <p class="text-sm text-blue-700 mt-1">
                      Controle se as empresas podem encontrar e ver o seu perfil nas pesquisas.
                    </p>
                    <div class="mt-3">
                      <label class="flex items-center">
                        <input
                          type="checkbox"
                          v-model="privacySettings.is_searchable"
                          @change="updateSearchability"
                          :disabled="updatingPrivacy"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <span class="ml-2 text-sm text-blue-900">
                          {{ privacySettings.is_searchable ? 'Perfil visível para empresas' : 'Perfil oculto para empresas' }}
                        </span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Consentimento de Email -->
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div class="ml-3 flex-1">
                    <h4 class="text-sm font-medium text-blue-900">
                      Consentimento para Emails
                    </h4>
                    <p class="text-sm text-blue-700 mt-1">
                      Autorize o envio de emails sobre o andamento de suas candidaturas.
                    </p>
                    <div class="mt-3">
                      <label class="flex items-center">
                        <input
                          type="checkbox"
                          v-model="privacySettings.consentimento_emails"
                          @change="updateEmailConsent"
                          :disabled="updatingPrivacy"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <span class="ml-2 text-sm text-blue-900">
                          {{ privacySettings.consentimento_emails ? 'Aceito receber emails' : 'Não desejo receber emails' }}
                        </span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Exportar Dados -->
              <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  <div class="ml-3 flex-1">
                    <h4 class="text-sm font-medium text-green-900">
                      Exportar Meus Dados
                    </h4>
                    <p class="text-sm text-green-700 mt-1">
                      Baixe uma cópia de todos os seus dados pessoais em formato JSON (conforme GDPR).
                    </p>
                    <div class="mt-3">
                      <button
                        @click="exportData"
                        :disabled="exportingData"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                      >
                        <svg v-if="exportingData" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ exportingData ? 'Exportando...' : 'Exportar Dados' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Zona de Perigo - Deletar Conta -->
              <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                  </div>
                  <div class="ml-3 flex-1">
                    <h4 class="text-sm font-medium text-red-900">
                      Zona de Perigo
                    </h4>
                    <p class="text-sm text-red-700 mt-1">
                      Esta ação não pode ser desfeita. Todos os seus dados serão permanentemente removidos.
                    </p>
                    <div class="mt-3">
                      <button
                        @click="showDeleteModal = true"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                      >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Deletar Conta Permanentemente
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Mensagens de feedback -->
              <div v-if="privacyMessage" class="rounded-md bg-green-50 p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                      {{ privacyMessage }}
                    </p>
                  </div>
                </div>
              </div>
              
              <div v-if="privacyError" class="rounded-md bg-red-50 p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">
                      {{ privacyError }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Modal de Confirmação para Deletar Conta -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
              <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            
            <h3 class="text-lg font-medium text-gray-900 text-center mb-4">
              Deletar Conta Permanentemente
            </h3>
            
            <div class="mb-4">
              <p class="text-sm text-gray-500 mb-4">
                Esta ação é <strong>irreversível</strong>. Todos os seus dados serão permanentemente removidos, incluindo:
              </p>
              <ul class="text-sm text-gray-500 list-disc list-inside space-y-1 mb-4">
                <li>Perfil e informações pessoais</li>
                <li>CV e documentos</li>
                <li>Histórico de candidaturas</li>
                <li>Configurações e preferências</li>
              </ul>
              
              <div class="space-y-3">
                <div>
                  <label for="deletePassword" class="block text-sm font-medium text-gray-700">
                    Digite sua senha para confirmar:
                  </label>
                  <input
                    type="password"
                    id="deletePassword"
                    v-model="deleteForm.password"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                    placeholder="Sua senha atual"
                  />
                </div>
                
                <div>
                  <label for="deleteConfirmation" class="block text-sm font-medium text-gray-700">
                    Digite "DELETE_MY_ACCOUNT" para confirmar:
                  </label>
                  <input
                    type="text"
                    id="deleteConfirmation"
                    v-model="deleteForm.confirmation"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                    placeholder="DELETE_MY_ACCOUNT"
                  />
                </div>
              </div>
            </div>
            
            <div class="flex gap-3">
              <button
                @click="closeDeleteModal"
                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium"
              >
                Cancelar
              </button>
              <button
                @click="deleteAccount"
                :disabled="!canDeleteAccount || deletingAccount"
                class="flex-1 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm font-medium"
              >
                <span v-if="deletingAccount" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                {{ deletingAccount ? 'Deletando...' : 'Confirmar Exclusão' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { candidatoAuthService } from '../../services/candidatoAuth'
import CandidatoNav from '@/components/CandidatoNav.vue'

const router = useRouter()
const loading = ref(true)
const updating = ref(false)
const updatingPrivacy = ref(false)
const exportingData = ref(false)
const deletingAccount = ref(false)
const showDeleteModal = ref(false)
const error = ref('')
const updateError = ref('')
const updateSuccess = ref('')
const privacyError = ref('')
const privacyMessage = ref('')
const candidato = ref<any>(null)
const selectedFile = ref<File | null>(null)
const apiBaseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
const isColaborador = ref(false)

// Profile photo upload state
const profilePhotoInput = ref<HTMLInputElement>()
const uploading = ref(false)
const photoError = ref('')
const photoSuccess = ref('')

const form = ref({
  nome: '',
  apelido: '',
  email: '',
  telefone: '',
  data_nascimento: '',
  linkedin_url: ''
})

const privacySettings = ref({
  is_searchable: true,
  consentimento_emails: false
})

const deleteForm = ref({
  password: '',
  confirmation: ''
})

// Computed para validar se pode deletar a conta
const canDeleteAccount = computed(() => {
  return deleteForm.value.password.trim() !== '' && 
         deleteForm.value.confirmation.trim() === 'DELETE_MY_ACCOUNT'
})

// Computed para URL da foto de perfil atual
const currentProfilePhoto = computed(() => {
  if (candidato.value?.profile_photo) {
    return `${apiBaseUrl}/storage/${candidato.value.profile_photo}`
  }
  // Fallback para iniciais do nome
  const initials = form.value.nome && form.value.apelido 
    ? `${form.value.nome.charAt(0)}${form.value.apelido.charAt(0)}`.toUpperCase()
    : 'UN'
  return `https://ui-avatars.com/api/?name=${initials}&background=0D8ABC&color=fff&size=100&rounded=true`
})

const checkColaboradorAccess = async () => {
  try {
    // Importar colaboradorService dinamicamente para evitar dependências circulares
    const colaboradorService = (await import('../../services/colaboradorService')).default
    console.log('Verificando acesso de colaborador...')
    const result = await colaboradorService.verificarAcesso()
    console.log('Resultado da verificação:', result)
    isColaborador.value = result.is_colaborador
    console.log('isColaborador definido como:', isColaborador.value)
  } catch (error) {
    console.log('Erro ao verificar acesso de colaborador:', error)
    console.log('Candidato não é colaborador:', error)
    isColaborador.value = false
  }
}

const goToColaboradorArea = () => {
  router.push('/colaborador/dashboard')
}

const loadProfile = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const candidatoData = await candidatoAuthService.me()
    candidato.value = candidatoData
    
    console.log('Dados carregados do servidor:', candidatoData)
    
    // Preencher o formulário com os dados atuais - usando nextTick para garantir reatividade
    await nextTick()
    form.value = {
      nome: candidatoData.nome || '',
      apelido: candidatoData.apelido || '',
      email: candidatoData.email || '',
      telefone: candidatoData.telefone || '',
      data_nascimento: candidatoData.data_nascimento || '',
      linkedin_url: candidatoData.linkedin_url || ''
    }
    
    console.log('Formulário atualizado:', form.value)
    
    // Carregar configurações de privacidade
    privacySettings.value = {
      is_searchable: Boolean(candidatoData.is_searchable),
      consentimento_emails: Boolean(candidatoData.consentimento_emails)
    }
    
    console.log('Configurações de privacidade:', privacySettings.value)
    console.log('is_searchable raw value:', candidatoData.is_searchable)
    console.log('is_searchable converted:', Boolean(candidatoData.is_searchable))
    console.log('consentimento_emails raw value:', candidatoData.consentimento_emails)
    console.log('consentimento_emails converted:', Boolean(candidatoData.consentimento_emails))
    
  } catch (err: any) {
    console.error('Erro ao carregar perfil:', err)
    error.value = err.response?.data?.message || 'Erro ao carregar perfil'
  } finally {
    loading.value = false
  }
}

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    // Validar tipo de arquivo
    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
    if (!allowedTypes.includes(file.type)) {
      updateError.value = 'Tipo de arquivo não suportado. Use PDF, DOC ou DOCX.'
      target.value = ''
      return
    }
    
    // Validar tamanho (10MB)
    if (file.size > 10 * 1024 * 1024) {
      updateError.value = 'Arquivo muito grande. Tamanho máximo: 10MB.'
      target.value = ''
      return
    }
    
    selectedFile.value = file
    updateError.value = ''
  }
}

const updateProfile = async () => {
  updating.value = true
  updateError.value = ''
  updateSuccess.value = ''
  
  try {
    console.log('Estado atual do form:', form.value)
    
    const formData = new FormData()
    
    // Adicionar todos os dados do formulário que não são undefined ou null
    // Permitir strings vazias pois podem ser intencionais (como limpar um campo opcional)
    Object.keys(form.value).forEach(key => {
      const value = form.value[key as keyof typeof form.value]
      if (value !== undefined && value !== null) {
        console.log(`Adicionando campo ${key}:`, value)
        formData.append(key, value)
      }
    })
    
    // Adicionar arquivo se selecionado
    if (selectedFile.value) {
      console.log('Adicionando arquivo CV:', selectedFile.value.name)
      formData.append('cv', selectedFile.value)
    }
    
    // Log dos dados sendo enviados para debug
    console.log('FormData entries:', Array.from(formData.entries()))
    
    const response = await candidatoAuthService.updateProfile(formData)
    
    console.log('Resposta da API de atualização:', response)
    
    updateSuccess.value = 'Perfil atualizado com sucesso!'
    
    // Recarregar dados do servidor para garantir sincronização
    await loadProfile()
    
    // Limpar arquivo selecionado
    selectedFile.value = null
    const fileInput = document.getElementById('cv') as HTMLInputElement
    if (fileInput) {
      fileInput.value = ''
    }
    
    // Limpar mensagem de sucesso após 3 segundos
    setTimeout(() => {
      updateSuccess.value = ''
    }, 3000)
    
  } catch (err: any) {
    console.error('Erro ao atualizar perfil:', err)
    updateError.value = err.response?.data?.message || 'Erro ao atualizar perfil'
  } finally {
    updating.value = false
  }
}

const updateSearchability = async () => {
  updatingPrivacy.value = true
  privacyError.value = ''
  privacyMessage.value = ''
  
  try {
    console.log('Atualizando visibilidade para:', privacySettings.value.is_searchable)
    
    await candidatoAuthService.updatePrivacySettings({
      is_searchable: privacySettings.value.is_searchable,
      consentimento_emails: privacySettings.value.consentimento_emails
    })
    
    privacyMessage.value = 'Configurações de privacidade atualizadas com sucesso!'
    
    // Recarregar dados do servidor para garantir sincronização
    await loadProfile()
    
    // Limpar mensagem de sucesso após 3 segundos
    setTimeout(() => {
      privacyMessage.value = ''
    }, 3000)
    
  } catch (err: any) {
    console.error('Erro ao atualizar configurações de privacidade:', err)
    privacyError.value = err.response?.data?.message || 'Erro ao atualizar configurações de privacidade'
    
    // Reverter o valor do checkbox em caso de erro
    await loadProfile()
  } finally {
    updatingPrivacy.value = false
  }
}

const toggleConsentimento = (event: Event) => {
  const target = event.target as HTMLInputElement
  privacySettings.value.consentimento_emails = !target.checked
  updateEmailConsent()
}

const updateEmailConsent = async () => {
  updatingPrivacy.value = true
  privacyError.value = ''
  privacyMessage.value = ''
  
  try {
    console.log('Atualizando consentimento de email para:', privacySettings.value.consentimento_emails)
    
    await candidatoAuthService.updatePrivacySettings({
      is_searchable: privacySettings.value.is_searchable,
      consentimento_emails: privacySettings.value.consentimento_emails
    })
    
    privacyMessage.value = 'Configurações de email atualizadas com sucesso!'
    
    // Recarregar dados do servidor para garantir sincronização
    await loadProfile()
    
    // Limpar mensagem de sucesso após 3 segundos
    setTimeout(() => {
      privacyMessage.value = ''
    }, 3000)
    
  } catch (err: any) {
    console.error('Erro ao atualizar consentimento de email:', err)
    privacyError.value = err.response?.data?.message || 'Erro ao atualizar consentimento de email'
    
    // Reverter o valor do checkbox em caso de erro
    await loadProfile()
  } finally {
    updatingPrivacy.value = false
  }
}

const exportData = async () => {
  exportingData.value = true
  privacyError.value = ''
  privacyMessage.value = ''
  
  try {
    const response = await candidatoAuthService.exportData()
    
    // Criar link para download do arquivo JSON
    const url = window.URL.createObjectURL(response.data)
    const a = document.createElement('a')
    a.href = url
    a.setAttribute('download', `meus_dados_${new Date().toISOString().slice(0, 10)}.json`)
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
    
    privacyMessage.value = 'Dados exportados com sucesso! Iniciando download...'
    
  } catch (err: any) {
    privacyError.value = err.response?.data?.message || 'Erro ao exportar dados'
  } finally {
    exportingData.value = false
  }
}

const deleteAccount = async () => {
  deletingAccount.value = true
  privacyError.value = ''
  
  try {
    console.log('Tentando deletar conta com dados:', {
      password: deleteForm.value.password ? '***' : 'vazio',
      confirmation: deleteForm.value.confirmation
    })
    
    // Validar senha e confirmação
    if (deleteForm.value.password.trim() === '') {
      privacyError.value = 'Senha é obrigatória'
      return
    }
    
    if (deleteForm.value.confirmation.trim() !== 'DELETE_MY_ACCOUNT') {
      privacyError.value = 'Digite exatamente "DELETE_MY_ACCOUNT" para confirmar'
      return
    }
    
    console.log('Validação passou, enviando requisição...')
    
    await candidatoAuthService.deleteAccount({
      password: deleteForm.value.password,
      confirmation: deleteForm.value.confirmation
    })
    
    console.log('Conta deletada com sucesso')
    
    // Redirecionar ou encerrar sessão após exclusão
    window.location.href = '/'
    
  } catch (err: any) {
    console.error('Erro ao deletar conta:', err)
    privacyError.value = err.response?.data?.message || 'Erro ao deletar conta'
  } finally {
    deletingAccount.value = false
  }
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  deleteForm.value = {
    password: '',
    confirmation: ''
  }
}

// Profile photo methods
const triggerProfilePhotoUpload = () => {
  profilePhotoInput.value?.click()
}

const handleProfilePhotoChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    // Validar tipo de arquivo
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
    if (!allowedTypes.includes(file.type)) {
      photoError.value = 'Tipo de arquivo não suportado. Use JPEG, PNG, JPG ou GIF.'
      target.value = ''
      return
    }
    
    // Validar tamanho (2MB)
    if (file.size > 2 * 1024 * 1024) {
      photoError.value = 'Arquivo muito grande. Tamanho máximo: 2MB.'
      target.value = ''
      return
    }
    
    uploadProfilePhoto(file)
  }
}

const uploadProfilePhoto = async (file: File) => {
  uploading.value = true
  photoError.value = ''
  photoSuccess.value = ''
  
  try {
    const formData = new FormData()
    formData.append('profile_photo', file)
    
    const response = await candidatoAuthService.uploadProfilePhoto(formData)
    
    // Atualizar os dados do candidato com a nova foto
    candidato.value = { ...candidato.value, ...response.candidato }
    
    photoSuccess.value = 'Foto de perfil atualizada com sucesso!'
    
    // Limpar mensagem de sucesso após 3 segundos
    setTimeout(() => {
      photoSuccess.value = ''
    }, 3000)
    
  } catch (err: any) {
    console.error('Erro ao fazer upload da foto:', err)
    photoError.value = err.response?.data?.message || 'Erro ao fazer upload da foto'
  } finally {
    uploading.value = false
    // Limpar o input
    if (profilePhotoInput.value) {
      profilePhotoInput.value.value = ''
    }
  }
}

const removeProfilePhoto = async () => {
  uploading.value = true
  photoError.value = ''
  photoSuccess.value = ''
  
  try {
    const response = await candidatoAuthService.removeProfilePhoto()
    
    // Atualizar os dados do candidato removendo a foto
    candidato.value = { ...candidato.value, ...response.candidato }
    
    photoSuccess.value = 'Foto de perfil removida com sucesso!'
    
    // Limpar mensagem de sucesso após 3 segundos
    setTimeout(() => {
      photoSuccess.value = ''
    }, 3000)
    
  } catch (err: any) {
    console.error('Erro ao remover foto:', err)
    photoError.value = err.response?.data?.message || 'Erro ao remover foto'
  } finally {
    uploading.value = false
  }
}

const handlePhotoError = (event: Event) => {
  const target = event.target as HTMLImageElement
  // Fallback para iniciais caso a imagem falhe ao carregar
  const initials = form.value.nome && form.value.apelido 
    ? `${form.value.nome.charAt(0)}${form.value.apelido.charAt(0)}`.toUpperCase()
    : 'UN'
  target.src = `https://ui-avatars.com/api/?name=${initials}&background=0D8ABC&color=fff&size=100&rounded=true`
}

onMounted(() => {
  loadProfile()
  checkColaboradorAccess()
})
</script>
