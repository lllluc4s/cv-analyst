<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header (opcional) -->
    <div v-if="!hideHeader" class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
          <div class="flex items-center justify-between">
            <div>
              <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                  <li>
                    <router-link 
                      to="/empresas/dashboard" 
                      class="text-gray-400 hover:text-gray-500"
                    >
                      Dashboard
                    </router-link>
                  </li>
                  <li>
                    <div class="flex items-center">
                      <svg class="flex-shrink-0 h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                      <router-link 
                        :to="`/empresas/oportunidades/${oportunidadeId}/candidates`" 
                        class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                      >
                        Candidatos
                      </router-link>
                    </div>
                  </li>
                  <li>
                    <div class="flex items-center">
                      <svg class="flex-shrink-0 h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                      <span class="ml-4 text-sm font-medium text-gray-500">Board Kanban</span>
                    </div>
                  </li>
                </ol>
              </nav>
              <div class="mt-2 flex items-center justify-between">
                <div>
                  <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                    Board Kanban - {{ oportunidade?.titulo }}
                  </h1>
                  <p class="mt-1 text-sm text-gray-500">
                    Gerir candidatos por estados do processo seletivo
                  </p>
                </div>
                <div class="flex space-x-3">
                  <router-link 
                    :to="`/empresas/oportunidades/${oportunidadeId}/candidates`"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                  >
                    📋 Vista Lista
                  </router-link>
                  <button
                    @click="showStateManager = true"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                  >
                    ⚙️ Gerir Estados
                  </button>
                  <button
                    @click="showCreateStateModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                  >
                    ➕ Novo Estado
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Opportunity Tabs -->
      <OpportunityTabs :oportunidade-id="Number(oportunidadeId)" />

      <!-- Filtros -->
      <div class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="flex items-center space-x-4">
          <label class="text-sm font-medium text-gray-700">Filtrar candidatos por avaliação:</label>
          <select
            v-model="ratingFilter"
            @change="applyRatingFilter"
            class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Todas as avaliações</option>
            <option value="5">⭐⭐⭐⭐⭐ (5 estrelas)</option>
            <option value="4">⭐⭐⭐⭐ (4+ estrelas)</option>
            <option value="3">⭐⭐⭐ (3+ estrelas)</option>
            <option value="2">⭐⭐ (2+ estrelas)</option>
            <option value="1">⭐ (1+ estrelas)</option>
            <option value="unrated">Sem avaliação</option>
          </select>
        </div>
      </div>
      
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin mx-auto h-8 w-8 text-blue-600">
          <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <p class="mt-2 text-gray-500">A carregar board...</p>
      </div>

      <!-- Kanban Board -->
      <div v-else class="flex space-x-6 overflow-x-auto pb-6">
        <div 
          v-for="state in board" 
          :key="state.id" 
          class="flex-shrink-0 w-80"
        >
          <!-- Column Header -->
          <div 
            class="rounded-t-lg p-4 border-b-2"
            :style="{ backgroundColor: state.cor + '20', borderBottomColor: state.cor }"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div 
                  class="w-3 h-3 rounded-full mr-2"
                  :style="{ backgroundColor: state.cor }"
                ></div>
                <h3 class="font-semibold text-gray-900">
                  {{ state.nome }}
                </h3>
                <span class="ml-2 bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">
                  {{ state.candidaturas.length }}
                </span>
              </div>
              <div class="flex space-x-1">
                <button
                  @click="configureStateEmails(state)"
                  class="text-gray-500 hover:text-blue-600 p-1"
                  title="Configurar Emails Automáticos"
                >
                  📧
                </button>
                <button
                  v-if="canEditState(state)"
                  @click="editState(state)"
                  class="text-gray-500 hover:text-gray-700 p-1"
                  title="Editar Estado"
                >
                  ✏️
                </button>
                <button
                  v-if="canDeleteState(state)"
                  @click="deleteState(state.id)"
                  class="text-gray-500 hover:text-red-600 p-1"
                  title="Remover Estado"
                >
                  🗑️
                </button>
              </div>
            </div>
          </div>

          <!-- Cards Container -->
          <div 
            class="bg-gray-100 min-h-96 p-4 rounded-b-lg"
            @drop="onDrop($event, state.id)"
            @dragover.prevent
            @dragenter.prevent
          >
            <!-- Candidate Cards -->
            <div 
              v-for="candidatura in state.candidaturas" 
              :key="candidatura.id"
              :draggable="true"
              @dragstart="onDragStart($event, candidatura)"
              @click="openCandidateDetails(candidatura)"
              class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-3 cursor-pointer hover:shadow-md transition-all duration-200 hover:border-blue-300"
            >
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center">
                  <!-- Avatar/Photo -->
                  <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                    <span class="text-sm font-bold text-white">
                      {{ candidatura.nome.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                  <div class="min-w-0 flex-1">
                    <h4 class="text-sm font-medium text-gray-900 truncate">
                      {{ candidatura.nome }}
                    </h4>
                    <p class="text-xs text-gray-500 truncate">
                      {{ candidatura.email }}
                    </p>
                  </div>
                </div>
                <div class="flex space-x-1 flex-shrink-0">
                  <button
                    @click.stop="fetchCandidateHistory(candidatura.id)"
                    class="text-gray-400 hover:text-green-600 p-1 transition-colors"
                    title="Ver Histórico"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  
                  <!-- Botão de exclusão direto -->
                  <button
                    @click.stop="deleteCandidate(candidatura)"
                    class="text-gray-400 hover:text-red-600 p-1 transition-colors"
                    title="Excluir Candidato"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
              
              <!-- Contact Info -->
              <div class="space-y-1 mb-3">
                <div v-if="candidatura.telefone" class="flex items-center text-xs text-gray-500">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  {{ candidatura.telefone }}
                </div>
              </div>

              <!-- Skills Preview -->
              <div v-if="candidatura.skills_extraidas && candidatura.skills_extraidas.length" class="mb-3">
                <div class="flex flex-wrap gap-1">
                  <span 
                    v-for="skill in candidatura.skills_extraidas.slice(0, 3)" 
                    :key="skill"
                    class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full"
                  >
                    {{ skill }}
                  </span>
                  <span 
                    v-if="candidatura.skills_extraidas.length > 3"
                    class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full"
                  >
                    +{{ candidatura.skills_extraidas.length - 3 }}
                  </span>
                </div>
              </div>
              
              <!-- Bottom Info -->
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center text-xs text-gray-500">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ formatDate(candidatura.created_at) }}
                </div>
                <div v-if="candidatura.pontuacao_skills" class="text-xs">
                  <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">
                    {{ candidatura.pontuacao_skills }}
                  </span>
                </div>
              </div>
              
              <!-- Avaliação com Estrelas -->
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-600">Avaliação:</span>
                <div @click.stop>
                  <StarRating 
                    :rating="candidatura.company_rating"
                    @update="(rating) => updateCandidateRating(candidatura.id, rating)"
                    size="sm"
                  />
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="state.candidaturas.length === 0" class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <p class="mt-2 text-sm">
                Nenhum candidato
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create State Modal -->
    <div v-if="showCreateStateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Criar Novo Estado</h3>
          <form @submit.prevent="createState">
            <div class="space-y-4">
              <!-- Nome do Estado -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Estado</label>
                <input
                  v-model="newState.nome"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  placeholder="Ex: Teste Técnico"
                >
              </div>
              
              <!-- Cor -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cor</label>
                <div class="flex space-x-2">
                  <input
                    v-model="newState.cor"
                    type="color"
                    class="w-12 h-10 border border-gray-300 rounded cursor-pointer"
                  >
                  <input
                    v-model="newState.cor"
                    type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="#3B82F6"
                  >
                </div>
              </div>

              <!-- Email Automático -->
              <div class="flex items-center">
                <input
                  id="new-email-enabled"
                  v-model="newState.email_enabled"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <label for="new-email-enabled" class="ml-3 block text-sm font-medium text-gray-700">
                  Enviar email automático quando candidato entrar neste estado
                </label>
              </div>

              <!-- Configurações de Email -->
              <div v-if="newState.email_enabled" class="space-y-3 bg-gray-50 p-3 rounded-md">
                <div>
                  <label for="new-email-subject" class="block text-sm font-medium text-gray-700">
                    Assunto do Email
                  </label>
                  <input
                    id="new-email-subject"
                    v-model="newState.email_subject"
                    type="text"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ex: Atualização sobre sua candidatura em {oportunidade}"
                  >
                </div>

                <div>
                  <label for="new-email-body" class="block text-sm font-medium text-gray-700">
                    Corpo do Email
                  </label>
                  <textarea
                    id="new-email-body"
                    v-model="newState.email_body"
                    rows="4"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ex: Olá {nome}, sua candidatura para {oportunidade} teve o status atualizado..."
                  ></textarea>
                  
                  <!-- Placeholders disponíveis -->
                  <div class="mt-2 text-sm text-gray-500">
                    <p class="font-medium">Placeholders disponíveis:</p>
                    <p><code class="bg-gray-100 px-1 rounded">{nome}</code> - Nome do candidato</p>
                    <p><code class="bg-gray-100 px-1 rounded">{oportunidade}</code> - Título da oportunidade</p>
                    <p><code class="bg-gray-100 px-1 rounded">{empresa}</code> - Nome da empresa</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="showCreateStateModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="!newState.nome || !newState.cor"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
              >
                Criar Estado
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Email Configuration Modal -->
    <div v-if="showEmailConfigModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Configurar Emails Automáticos - {{ selectedState?.nome }}
          </h3>
          
          <form @submit.prevent="saveEmailConfig">
            <div class="space-y-4">
              <!-- Toggle Email Enabled -->
              <div class="flex items-center">
                <input
                  id="email-enabled"
                  v-model="emailConfig.email_enabled"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                >
                <label for="email-enabled" class="ml-3 block text-sm font-medium text-gray-700">
                  Enviar email automático quando candidato for movido para este estado
                </label>
              </div>

              <!-- Email Subject -->
              <div v-if="emailConfig.email_enabled">
                <label for="email-subject" class="block text-sm font-medium text-gray-700">
                  Assunto do Email
                </label>
                <input
                  id="email-subject"
                  v-model="emailConfig.email_subject"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                  placeholder="Ex: Atualização sobre sua candidatura em {oportunidade}"
                >
              </div>

              <!-- Email Body -->
              <div v-if="emailConfig.email_enabled">
                <label for="email-body" class="block text-sm font-medium text-gray-700">
                  Corpo do Email
                </label>
                <textarea
                  id="email-body"
                  v-model="emailConfig.email_body"
                  rows="8"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                  placeholder="Ex: Olá {nome}, informamos que sua candidatura para {oportunidade} na {empresa} teve seu status atualizado..."
                ></textarea>
                
                <!-- Available Placeholders -->
                <div class="mt-2 text-sm text-gray-500">
                  <p class="font-medium">Placeholders disponíveis:</p>
                  <p><code class="bg-gray-100 px-1 rounded">{nome}</code> - Nome do candidato</p>
                  <p><code class="bg-gray-100 px-1 rounded">{oportunidade}</code> - Título da oportunidade</p>
                  <p><code class="bg-gray-100 px-1 rounded">{empresa}</code> - Nome da empresa</p>
                  <p><code class="bg-gray-100 px-1 rounded">{link}</code> - Link para a oportunidade</p>
                </div>
              </div>

              <!-- Preview Button -->
              <div v-if="emailConfig.email_enabled && emailConfig.email_subject && emailConfig.email_body">
                <button
                  type="button"
                  @click="previewEmail"
                  class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                >
                  Visualizar Preview
                </button>
              </div>

              <!-- Preview Content -->
              <div v-if="emailPreview" class="mt-4 p-4 bg-gray-50 rounded-md">
                <h4 class="font-medium text-gray-900">Preview do Email:</h4>
                <div class="mt-2">
                  <p class="text-sm text-gray-700"><strong>Assunto:</strong> {{ emailPreview.subject }}</p>
                  <div class="mt-2 text-sm text-gray-700">
                    <strong>Corpo:</strong>
                    <div class="mt-1 whitespace-pre-wrap">{{ emailPreview.body }}</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="showEmailConfigModal = false"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
              >
                Cancelar
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
              >
                Salvar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="message.show" class="fixed bottom-4 right-4 z-50">
      <div 
        :class="[
          'px-4 py-3 rounded-md shadow-lg',
          message.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        ]"
      >
        {{ message.text }}
      </div>
    </div>

    <!-- Candidate Detail Modal -->
    <CandidateDetailModal
      :show="showCandidateModal"
      :candidatura="selectedCandidate"
      @close="closeCandidateModal"
      @updateNotes="updateCandidateNotes"
      @updateRating="updateCandidateRating"
      @showHistory="fetchCandidateHistory"
    />

    <!-- Move Candidate Modal -->
    <div v-if="showMoveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Mover Candidato</h3>
          <p class="text-sm text-gray-500 mb-4">
            Movendo <strong>{{ candidateToMove?.nome }}</strong> para outro estado.
          </p>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Nota (opcional)</label>
            <textarea
              v-model="moveNote"
              placeholder="Adicione uma nota sobre esta movimentação..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
              rows="3"
            ></textarea>
          </div>
          
          <div class="flex justify-end space-x-2">
            <button
              @click="showMoveModal = false"
              type="button"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Cancelar
            </button>
            <button
              @click="confirmMove"
              type="button"
              class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
            >
              Mover
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Candidate History Modal -->
    <div v-if="showHistoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="showHistoryModal = false">
      <div class="relative top-10 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Histórico do Candidato</h3>
            <button
              @click="showHistoryModal = false"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <!-- Debug Info -->
          <div class="mb-4 p-2 bg-blue-50 text-blue-700 text-sm">
            Total de entradas no histórico: {{ candidateHistory.length }}
          </div>
          
          <div class="max-h-96 overflow-y-auto">
            <div v-if="candidateHistory.length === 0" class="text-center py-8 text-gray-500">
              <p>Nenhum histórico encontrado</p>
              <p class="text-xs mt-2">Os dados devem aparecer quando o candidato for movido</p>
            </div>
            
            <div v-else class="space-y-4">
              <div
                v-for="entry in candidateHistory"
                :key="entry.id"
                class="border rounded-lg p-4 bg-gray-50"
              >
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <span class="font-medium text-gray-900">
                      {{ entry.from_state ? `${entry.from_state} → ${entry.to_state}` : `Movido para ${entry.to_state}` }}
                    </span>
                    <span class="text-sm text-gray-500 ml-2">
                      {{ formatDate(entry.moved_at) }}
                    </span>
                  </div>
                  <div class="flex space-x-2">
                    <span v-if="entry.email_sent" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      Email enviado
                    </span>
                  </div>
                </div>
                
                <div v-if="entry.note" class="mb-3">
                  <p class="text-sm text-gray-700 bg-white p-2 rounded border">
                    <strong>Nota:</strong> {{ entry.note }}
                  </p>
                </div>
                
                <div v-if="entry.email_sent && entry.email_data" class="text-sm">
                  <details class="cursor-pointer">
                    <summary class="text-indigo-600 hover:text-indigo-800">Ver email enviado</summary>
                    <div class="mt-2 p-3 bg-white rounded border">
                      <p><strong>Assunto:</strong> {{ entry.email_data.subject }}</p>
                      <p class="mt-2"><strong>Corpo:</strong></p>
                      <div class="mt-1 p-2 bg-gray-50 rounded text-sm">
                        {{ entry.email_data.body }}
                      </div>
                    </div>
                  </details>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal do Gerenciador de Estados -->
    <div v-if="showStateManager" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="showStateManager = false">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white" @click.stop>
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Gerenciar Estados da Oportunidade</h3>
          <button 
            @click="showStateManager = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <StateManager 
          :oportunidade-id="Number(oportunidadeId)" 
          @stateChanged="fetchBoard"
        />
      </div>
    </div>

    <!-- Modal de Edição de Estado -->
    <div v-if="showEditStateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="cancelEditState">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Editar Estado</h3>
          <button 
            @click="cancelEditState"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="saveEditState" class="space-y-4">
          <!-- Nome do Estado -->
          <div>
            <label for="edit-state-name" class="block text-sm font-medium text-gray-700 mb-1">
              Nome do Estado
            </label>
            <input 
              id="edit-state-name"
              v-model="editStateForm.nome"
              type="text" 
              required
              placeholder="Ex: Entrevista Técnica"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
          
          <!-- Cor -->
          <div>
            <label for="edit-state-color" class="block text-sm font-medium text-gray-700 mb-1">
              Cor
            </label>
            <div class="flex items-center space-x-2">
              <input 
                id="edit-state-color"
                v-model="editStateForm.cor"
                type="color" 
                class="w-16 h-10 border border-gray-300 rounded-md cursor-pointer"
              />
              <input 
                v-model="editStateForm.cor"
                type="text" 
                placeholder="#3B82F6"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
          </div>

          <!-- Email Automático -->
          <div>
            <div class="flex items-center">
              <input 
                id="edit-email-enabled"
                v-model="editStateForm.email_enabled"
                type="checkbox" 
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <label for="edit-email-enabled" class="ml-2 block text-sm text-gray-900">
                Enviar email automático quando candidato entrar neste estado
              </label>
            </div>
          </div>

          <!-- Configurações de Email (se habilitado) -->
          <div v-if="editStateForm.email_enabled" class="space-y-3 bg-gray-50 p-3 rounded-md">
            <div>
              <label for="edit-email-subject" class="block text-sm font-medium text-gray-700 mb-1">
                Assunto do Email
              </label>
              <input 
                id="edit-email-subject"
                v-model="editStateForm.email_subject"
                type="text" 
                placeholder="Ex: Parabéns! Você passou para a próxima fase"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            
            <div>
              <label for="edit-email-body" class="block text-sm font-medium text-gray-700 mb-1">
                Corpo do Email
              </label>
              <textarea 
                id="edit-email-body"
                v-model="editStateForm.email_body"
                rows="4"
                placeholder="Olá {nome}, informamos que você passou para a próxima fase do processo seletivo para a vaga {vaga}..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              ></textarea>
              <p class="text-xs text-gray-500 mt-1">
                Use {nome} para o nome do candidato e {vaga} para o título da vaga
              </p>
            </div>
          </div>
          
          <!-- Botões -->
          <div class="flex justify-end space-x-3 pt-4">
            <button 
              type="button"
              @click="cancelEditState"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Cancelar
            </button>
            <button 
              type="submit"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Salvar
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import CandidateDetailModal from '../../components/CandidateDetailModal.vue'
import StateManager from '../../components/StateManager.vue'
import StarRating from '../../components/StarRating.vue'
import OpportunityTabs from '@/components/OpportunityTabs.vue'

// Props para uso como componente
interface Props {
  companyId?: number
  oportunidadeId?: number
  hideHeader?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  hideHeader: false
})

const route = useRoute()

// Usar props se fornecidas, senão usar route params
const companyId = computed(() => props.companyId || route.params.companyId)
const oportunidadeId = computed(() => props.oportunidadeId || route.params.id)
// O parâmetro pode ser um slug ou um ID numérico, passamos diretamente para a API

const loading = ref(true)
const oportunidade = ref<any>(null)
const board = ref<any[]>([])
const originalBoard = ref<any[]>([]) // Backup dos dados originais
const ratingFilter = ref('')
const showCreateStateModal = ref(false)
const showStateManager = ref(false)
const showEditStateModal = ref(false)
const selectedStateToEdit = ref<any>(null)
const editStateForm = ref({
  nome: '',
  cor: '#3B82F6',
  email_enabled: false,
  email_subject: '',
  email_body: ''
})
const newState = ref({
  nome: '',
  cor: '#3B82F6',
  email_enabled: false,
  email_subject: '',
  email_body: ''
})
const message = ref({
  show: false,
  text: '',
  type: 'success'
})
const selectedState = ref<any>(null)
const showEmailConfigModal = ref(false)
const emailConfig = ref({
  email_enabled: false,
  email_subject: '',
  email_body: ''
})
const emailPreview = ref<any>(null)

// Função para abrir CV
// Modal de detalhes do candidato
const showCandidateModal = ref(false)
const selectedCandidate = ref<any>(null)

// Modal para mover candidato com nota
const showMoveModal = ref(false)
const candidateToMove = ref<any>(null)
const targetStateId = ref<number | null>(null)
const moveNote = ref('')

// Histórico do candidato
const candidateHistory = ref<any[]>([])
const showHistoryModal = ref(false)

// Estados relacionados aos candidatos

const openCandidateDetails = (candidatura: any) => {
  selectedCandidate.value = candidatura
  showCandidateModal.value = true
}

const closeCandidateModal = () => {
  showCandidateModal.value = false
  selectedCandidate.value = null
}

const updateCandidateNotes = async (candidaturaId: number, notes: string) => {
  try {
    await axios.put(`${import.meta.env.VITE_API_URL}/api/companies/kanban/candidaturas/${candidaturaId}/notes`, {
      notes: notes
    }, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('company_token')}`
      }
    })
    
    // Update local data
    if (selectedCandidate.value) {
      selectedCandidate.value.notas_privadas = notes
    }
    
    showMessage('Notas atualizadas com sucesso!', 'success')
  } catch (error) {
    console.error('Erro ao atualizar notas:', error)
    showMessage('Erro ao atualizar notas', 'error')
  }
}

let refreshInterval: number | null = null

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-PT', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const showMessage = (text: string, type: 'success' | 'error' = 'success') => {
  message.value = { show: true, text, type }
  setTimeout(() => {
    message.value.show = false
  }, 3000)
}

const fetchBoard = async () => {
  try {
    const token = localStorage.getItem('company_token')
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/companies/kanban/oportunidades/${oportunidadeId.value}/board`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'ngrok-skip-browser-warning': 'true'
      }
    })
    
    oportunidade.value = response.data.oportunidade
    board.value = response.data.board
    originalBoard.value = JSON.parse(JSON.stringify(response.data.board)) // Salvar cópia dos dados originais
    applyRatingFilter() // Aplicar filtro se existe
  } catch (error) {
    console.error('Erro ao buscar board:', error)
    showMessage('Erro ao carregar o board', 'error')
  } finally {
    loading.value = false
  }
}

const onDragStart = (event: DragEvent, candidatura: any) => {
  if (event.dataTransfer) {
    event.dataTransfer.setData('application/json', JSON.stringify(candidatura))
  }
}

const onDrop = async (event: DragEvent, stateId: number) => {
  event.preventDefault()
  
  if (!event.dataTransfer) return
  
  const candidaturaData = JSON.parse(event.dataTransfer.getData('application/json'))
  
  // Mostrar modal para adicionar nota
  candidateToMove.value = candidaturaData
  targetStateId.value = stateId
  moveNote.value = ''
  showMoveModal.value = true
}

const confirmMove = async () => {
  if (!candidateToMove.value || !targetStateId.value) return
  
  try {
    const token = localStorage.getItem('company_token')
    await axios.post(`${import.meta.env.VITE_API_URL}/api/companies/kanban/candidaturas/move`, {
      candidatura_id: candidateToMove.value.id,
      to_stage_id: targetStateId.value,
      note: moveNote.value || null
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    
    // Refresh board
    await fetchBoard()
    showMessage('Candidato movido com sucesso!')
    
    // Fechar modal
    showMoveModal.value = false
    candidateToMove.value = null
    targetStateId.value = null
    moveNote.value = ''
  } catch (error) {
    console.error('Erro ao mover candidato:', error)
    showMessage('Erro ao mover candidato', 'error')
  }
}

const createState = async () => {
  try {
    const token = localStorage.getItem('company_token')
    
    // Adicionar campos obrigatórios de email se não estiverem presentes
    const stateData = {
      ...newState.value,
      email_enabled: newState.value.email_enabled || false,
      email_subject: newState.value.email_subject || '',
      email_body: newState.value.email_body || ''
    }
    
    await axios.post(`${import.meta.env.VITE_API_URL}/api/companies/kanban/oportunidades/${oportunidadeId.value}/states`, stateData, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    
    showCreateStateModal.value = false
    newState.value = { 
      nome: '', 
      cor: '#3B82F6',
      email_enabled: false,
      email_subject: '',
      email_body: ''
    }
    await fetchBoard()
    showMessage('Estado criado com sucesso!')
  } catch (error: any) {
    console.error('Erro ao criar estado:', error)
    
    // Melhor tratamento de erro
    let errorMessage = 'Erro ao criar estado'
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.response?.status === 400) {
      errorMessage = 'Dados inválidos para criação do estado'
    } else if (error.response?.status === 403) {
      errorMessage = 'Você não tem permissão para criar estados'
    }
    
    showMessage(errorMessage, 'error')
  }
}

const editState = (state: any) => {
  // Abrir modal para editar o estado
  selectedStateToEdit.value = { ...state }
  editStateForm.value = {
    nome: state.nome,
    cor: state.cor,
    email_enabled: Boolean(state.email_enabled),
    email_subject: state.email_subject || '',
    email_body: state.email_body || ''
  }
  showEditStateModal.value = true
}

const saveEditState = async () => {
  if (!selectedStateToEdit.value || !editStateForm.value.nome.trim()) {
    showMessage('Nome do estado é obrigatório', 'error')
    return
  }
  
  try {
    const token = localStorage.getItem('company_token')
    await axios.put(`${import.meta.env.VITE_API_URL}/api/companies/kanban/oportunidades/${oportunidadeId.value}/states/${selectedStateToEdit.value.id}`, editStateForm.value, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    
    showEditStateModal.value = false
    selectedStateToEdit.value = null
    editStateForm.value = {
      nome: '',
      cor: '#3B82F6',
      email_enabled: false,
      email_subject: '',
      email_body: ''
    }
    await fetchBoard()
    showMessage('Estado atualizado com sucesso!')
  } catch (error) {
    console.error('Erro ao atualizar estado:', error)
    showMessage('Erro ao atualizar estado', 'error')
  }
}

const cancelEditState = () => {
  showEditStateModal.value = false
  selectedStateToEdit.value = null
  editStateForm.value = {
    nome: '',
    cor: '#3B82F6',
    email_enabled: false,
    email_subject: '',
    email_body: ''
  }
}

const deleteState = async (stateId: number) => {
  if (!confirm('Tem certeza que deseja remover este estado? Os candidatos serão movidos para o primeiro estado.')) {
    return
  }
  
  try {
    const token = localStorage.getItem('company_token')
    await axios.delete(`${import.meta.env.VITE_API_URL}/api/companies/kanban/oportunidades/${oportunidadeId.value}/states/${stateId}`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    
    await fetchBoard()
    showMessage('Estado removido com sucesso!')
  } catch (error: any) {
    console.error('Erro ao remover estado:', error)
    
    // Extrair mensagem de erro específica da resposta
    let errorMessage = 'Erro ao remover estado'
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.response?.status === 404) {
      errorMessage = 'Estado não encontrado ou não pode ser excluído'
    } else if (error.response?.status === 403) {
      errorMessage = 'Você não tem permissão para excluir este estado'
    }
    
    showMessage(errorMessage, 'error')
  }
}

// Configuração de emails
const configureStateEmails = (state: any) => {
  selectedState.value = state
  emailConfig.value = {
    email_enabled: Boolean(state.email_enabled),
    email_subject: state.email_subject || '',
    email_body: state.email_body || ''
  }
  emailPreview.value = null
  showEmailConfigModal.value = true
  
  console.log('Configurando emails para estado:', state.nome)
  console.log('Email enabled:', state.email_enabled, '-> Boolean:', Boolean(state.email_enabled))
  console.log('Email subject:', state.email_subject)
  console.log('Email body:', state.email_body)
}

const saveEmailConfig = async () => {
  try {
    console.log('Salvando configuração de email para o estado:', selectedState.value);
    console.log('Dados:', emailConfig.value);
    // Usar a rota de debug
    const response = await axios.put(`${import.meta.env.VITE_API_URL}/api/debug/kanban/email/${selectedState.value.id}`, emailConfig.value, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('company_token')}`
      }
    })
    
    // Update local state with response data
    const stateIndex = board.value.findIndex(s => s.id === selectedState.value.id)
    if (stateIndex !== -1) {
      Object.assign(board.value[stateIndex], {
        email_enabled: emailConfig.value.email_enabled,
        email_subject: emailConfig.value.email_subject,
        email_body: emailConfig.value.email_body
      })
    }
    
    // Also update selectedState for consistency
    if (selectedState.value) {
      Object.assign(selectedState.value, {
        email_enabled: emailConfig.value.email_enabled,
        email_subject: emailConfig.value.email_subject,
        email_body: emailConfig.value.email_body
      })
    }
    
    console.log('Estado atualizado localmente:', board.value[stateIndex])
    
    showEmailConfigModal.value = false
    showMessage('Configurações de email salvas com sucesso!', 'success')
  } catch (error) {
    console.error('Erro ao salvar configurações de email:', error)
    showMessage('Erro ao salvar configurações de email', 'error')
  }
}

const previewEmail = async () => {
  try {
    console.log('Gerando preview para o estado:', selectedState.value);
    // Usar a rota de debug
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/debug/kanban/email/${selectedState.value.id}/preview`, {
      email_subject: emailConfig.value.email_subject,
      email_body: emailConfig.value.email_body
    }, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('company_token')}`
      }
    })
    
    emailPreview.value = response.data.preview
  } catch (error) {
    console.error('Erro ao gerar preview:', error)
    showMessage('Erro ao gerar preview do email', 'error')
  }
}

const moveCandidate = async () => {
  if (!targetStateId.value) return
  
  try {
    const token = localStorage.getItem('company_token')
    await axios.post(`${import.meta.env.VITE_API_URL}/api/companies/kanban/candidaturas/move`, {
      candidatura_id: candidateToMove.value.id,
      to_stage_id: targetStateId.value,
      note: moveNote.value
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    
    // Refresh board
    await fetchBoard()
    showMessage('Candidato movido com sucesso!')
    
    // Close modal
    showMoveModal.value = false
    targetStateId.value = null
    moveNote.value = ''
  } catch (error) {
    console.error('Erro ao mover candidato:', error)
    showMessage('Erro ao mover candidato', 'error')
  }
}

const openMoveCandidateModal = (candidatura: any) => {
  candidateToMove.value = candidatura
  targetStateId.value = null
  moveNote.value = ''
  showMoveModal.value = true
  
  // Fetch candidate history
  fetchCandidateHistory(candidatura.id)
}

const fetchCandidateHistory = async (candidaturaId: number) => {
  try {
    console.log('=== DEBUG: Buscando histórico ===')
    console.log('Candidatura ID:', candidaturaId)
    
    const token = localStorage.getItem('company_token')
    console.log('Token:', token ? 'presente' : 'ausente')
    
    const url = `${import.meta.env.VITE_API_URL}/api/companies/kanban/candidaturas/${candidaturaId}/history`
    console.log('URL:', url)
    
    const response = await axios.get(url, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'ngrok-skip-browser-warning': 'true'
      }
    })
    
    console.log('=== RESPOSTA RECEBIDA ===')
    console.log('Status:', response.status)
    console.log('Data completa:', response.data)
    console.log('Histórico:', response.data.history)
    console.log('Número de entradas:', response.data.history?.length || 0)
    
    candidateHistory.value = response.data.history || []
    console.log('candidateHistory.value após atribuição:', candidateHistory.value)
    
    showHistoryModal.value = true
    console.log('Modal exibido:', showHistoryModal.value)
    
  } catch (error) {
    console.error('=== ERRO ao buscar histórico ===')
    console.error('Erro completo:', error)
    if (error instanceof Error && 'response' in error) {
      console.error('Resposta do erro:', (error as any).response?.data)
    }
    showMessage('Erro ao buscar histórico', 'error')
  }
}

const deleteCandidate = async (candidatura: any) => {
  if (!confirm(`Tem certeza que deseja excluir a candidatura de ${candidatura.nome}?`)) {
    return
  }
  
  try {
    const token = localStorage.getItem('company_token')
    const url = `${import.meta.env.VITE_API_URL}/api/candidaturas/${candidatura.slug}`
    
    console.log('=== DEBUG EXCLUSÃO ===')
    console.log('URL:', url)
    console.log('Token:', token ? 'presente' : 'ausente')
    console.log('Slug:', candidatura.slug)
    
    // Usar slug em vez de ID, conforme a rota do backend
    await axios.delete(url, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    
    await fetchBoard()
    showMessage('Candidatura excluída com sucesso!')
  } catch (error) {
    console.error('=== ERRO EXCLUSÃO ===')
    console.error('Erro completo:', error)
    if (error instanceof Error && 'response' in error) {
      console.error('Response:', (error as any).response?.data)
      console.error('Status:', (error as any).response?.status)
    }
    showMessage('Erro ao excluir candidatura', 'error')
  }
}

// Verificar se o estado pode ser editado
const canEditState = (state: any) => {
  // Permite editar todos os estados exceto os críticos do sistema
  const criticalStates = ['Recebido', 'Rejeitado']
  return !criticalStates.includes(state.nome)
}

// Verificar se o estado pode ser deletado
const canDeleteState = (state: any) => {
  // Permite deletar apenas estados personalizados (não padrão)
  // ou estados padrão que não são críticos
  const criticalStates = ['Recebido', 'Rejeitado']
  
  // Se não é estado padrão, pode deletar
  if (!state.is_default) {
    return true
  }
  
  // Se é estado padrão, só pode deletar se não for crítico
  return !criticalStates.includes(state.nome)
}

const updateCandidateRating = async (candidaturaId: number, rating: number) => {
  try {
    const token = localStorage.getItem('company_token')
    await axios.patch(`${import.meta.env.VITE_API_URL}/api/candidaturas/${candidaturaId}/rating`, {
      rating: rating
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    
    // Update local data
    board.value.forEach(state => {
      const candidatura = state.candidaturas.find((c: any) => c.id === candidaturaId)
      if (candidatura) {
        candidatura.company_rating = rating
      }
    })
    
    // Update original data
    originalBoard.value.forEach(state => {
      const candidatura = state.candidaturas.find((c: any) => c.id === candidaturaId)
      if (candidatura) {
        candidatura.company_rating = rating
      }
    })
    
    showMessage('Avaliação atualizada com sucesso!', 'success')
  } catch (error) {
    console.error('Erro ao atualizar avaliação:', error)
    showMessage('Erro ao atualizar avaliação', 'error')
  }
}

// Função para aplicar filtro de avaliação
const applyRatingFilter = () => {
  if (!ratingFilter.value) {
    // Se não há filtro, mostrar dados originais
    board.value = JSON.parse(JSON.stringify(originalBoard.value))
    return
  }
  
  // Criar cópia dos dados originais
  const filteredBoard = JSON.parse(JSON.stringify(originalBoard.value))
  
  // Filtrar candidaturas em cada estado
  filteredBoard.forEach((state: any) => {
    if (ratingFilter.value === 'unrated') {
      // Candidatos sem avaliação
      state.candidaturas = state.candidaturas.filter((c: any) => !c.company_rating)
    } else {
      // Candidatos com avaliação >= valor selecionado
      const minRating = parseInt(ratingFilter.value)
      state.candidaturas = state.candidaturas.filter((c: any) => c.company_rating && c.company_rating >= minRating)
    }
  })
  
  board.value = filteredBoard
}

onMounted(() => {
  fetchBoard()
  
  // Auto-refresh a cada 30 segundos
  refreshInterval = setInterval(() => {
    fetchBoard()
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>

<style scoped>
/* Animação para drag and drop */
.cursor-move:hover {
  transform: translateY(-2px);
  transition: transform 0.2s ease-in-out;
}
</style>
