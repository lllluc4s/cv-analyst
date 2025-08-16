<template>
  <div class="cv-analyzer-outer">
    <div class="cv-analyzer-flex">
      <!-- Upload Section -->
      <div class="card upload-card">
        <div class="card-header">
          <h2 class="card-title">Upload de Currículos</h2>
          <p class="card-description">Envie de 2 a 5 arquivos PDF para análise comparativa</p>
        </div>
        <div class="card-content">
          <div class="upload-area" @click="fileInput?.click()" @dragover.prevent @drop.prevent="handleDrop">
            <input
              ref="fileInput"
              type="file"
              multiple
              accept=".pdf"
              @change="handleFileSelect"
              class="hidden"
            />
            <!-- Removido o SVG da imagem acima do botão de upload -->
            <div class="text-center">
              <button class="btn btn-primary">
                Selecionar Arquivos PDF
              </button>
              <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6b7280;">
                Máximo 5MB por arquivo • Ou arraste arquivos aqui
              </p>
            </div>
          </div>
          <!-- File List -->
          <div v-if="selectedFiles.length > 0" class="file-list">
            <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 0.75rem 0; color: #374151;">
              Arquivos Selecionados ({{ selectedFiles.length }}):
            </h3>
            <div v-for="(file, index) in selectedFiles" :key="index" class="file-item">
              <div class="file-info">
                <svg class="icon text-red-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                </svg>
                <span class="file-name">{{ file.name }}</span>
                <span class="file-size">({{ formatFileSize(file.size) }})</span>
              </div>
              <button @click="removeFile(index)" class="btn btn-danger">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <!-- Analyze Button -->
            <button
              @click="analyzeCvs"
              :disabled="selectedFiles.length < 2 || isLoading"
              class="btn btn-success btn-full"
              style="margin-top: 1rem;"
            >
              <span v-if="isLoading" style="display: flex; align-items: center; justify-content: center;">
                <span class="spinner"></span>
                Analisando...
              </span>
              <span v-else>Analisar Currículos ({{ selectedFiles.length }})</span>
            </button>
          </div>
        </div>
      </div>
      <!-- Results Section -->
      <div v-if="analysisResults" class="card results-card">
        <div class="card-header">
          <h2 class="card-title">Resultados da Análise</h2>
        </div>
        <div class="card-content">
          <!-- Tabela Comparativa Detalhada -->
          <div style="margin-bottom: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin: 0 0 1rem 0;">
              Tabela Comparativa de Candidatos
            </h3>
            
            <div class="table-container">
              <table class="comparison-table">
                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Pontuação</th>
                    <th>Nº Skills</th>
                    <th>Skills Principais</th>
                    <th>Skills Exclusivas</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(candidate, index) in sortedCandidates" :key="index">
                    <td class="candidate-name">{{ candidate.nome }}</td>
                    <td class="candidate-email">{{ candidate.email }}</td>
                    <td class="candidate-score">
                      <span class="score-badge">{{ candidate.pontuacao }}</span>
                    </td>
                    <td class="skills-count">{{ candidate.skills.length }}</td>
                    <td class="skills-cell">
                      <div class="skills-list">
                        <span v-for="skill in candidate.skills.slice(0, 4)" :key="skill.skill" class="skill-tag small">
                          {{ skill.skill }}
                        </span>
                        <span v-if="candidate.skills.length > 4" class="more-skills">
                          +{{ candidate.skills.length - 4 }} mais
                        </span>
                      </div>
                    </td>
                    <td class="skills-cell">
                      <div class="skills-list">
                        <span v-for="uniqueSkill in getUniqueSkillsForCandidate(candidate.nome)" :key="uniqueSkill" class="skill-tag unique small">
                          {{ uniqueSkill }}
                        </span>
                        <span v-if="getUniqueSkillsForCandidate(candidate.nome).length === 0" class="no-skills">
                          Nenhuma
                        </span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Skills Comuns -->
          <div v-if="analysisResults.comparacao?.skills_comuns?.length > 0" style="margin-bottom: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin: 0 0 1rem 0;">
              Skills comuns entre Candidatos
            </h3>
            <div class="skills-container">
              <span v-for="skill in analysisResults.comparacao.skills_comuns" :key="skill" class="skill-tag common">
                {{ skill }}
              </span>
            </div>
          </div>

          <!-- Resumo Estatístico -->
          <div class="stats-summary">
            <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin: 0 0 1rem 0;">
              Resumo Estatístico
            </h3>
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-value">{{ analysisResults.candidatos.length }}</div>
                <div class="stat-label">Candidatos</div>
              </div>
              <div class="stat-card">
                <div class="stat-value">{{ getTotalUniqueSkills() }}</div>
                <div class="stat-label">Skills Únicas Total</div>
              </div>
              <div class="stat-card">
                <div class="stat-value">{{ analysisResults.comparacao?.skills_comuns?.length || 0 }}</div>
                <div class="stat-label">Skills Comuns</div>
              </div>
              <div class="stat-card">
                <div class="stat-value">{{ getHighestScore() }}</div>
                <div class="stat-label">Maior Pontuação</div>
              </div>
            </div>
          </div>

          <!-- Cards dos Candidatos (mantido para detalhes) -->
          <div style="margin-top: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; color: #1e293b; margin: 0 0 1rem 0;">
              Detalhes dos Candidatos
            </h3>
            
            <div class="candidate-grid">
              <div v-for="(candidate, index) in analysisResults.candidatos" :key="index" class="candidate-card">
                <div class="candidate-header">
                  <div class="candidate-info">
                    <h4>{{ candidate.nome }}</h4>
                    <p>{{ candidate.email }}</p>
                  </div>
                  <div class="score">
                    <div class="score-value">{{ candidate.pontuacao }}</div>
                    <div class="score-label">pontos</div>
                  </div>
                </div>
                
                <div class="skills-section">
                  <h5>Todas as Skills ({{ candidate.skills.length }}):</h5>
                  <div class="skills-container">
                    <span v-for="skill in candidate.skills" :key="skill.skill" class="skill-tag">
                      {{ skill.skill }}
                      <span class="skill-frequency">({{ skill.frequencia }}x)</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Error Message -->
    <div v-if="errorMessage" class="error">
      <svg class="error-icon" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
      </svg>
      <div class="error-content">
        <h3>Erro</h3>
        <p>{{ errorMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import axios from 'axios'

interface Skill {
  skill: string
  frequencia: number
}

interface Candidate {
  nome: string
  email: string
  pontuacao: number
  skills: Skill[]
}

interface Comparison {
  skills_comuns: string[]
  skills_unicas: { [key: string]: string[] }
}

interface AnalysisResults {
  candidatos: Candidate[]
  comparacao: Comparison
}

const selectedFiles = ref<File[]>([])
const isLoading = ref(false)
const analysisResults = ref<AnalysisResults | null>(null)
const errorMessage = ref('')
const fileInput = ref<HTMLInputElement | null>(null)

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files) {
    const files = Array.from(target.files)
    addFiles(files)
    target.value = ''
  }
}

const handleDrop = (event: DragEvent) => {
  if (event.dataTransfer?.files) {
    const files = Array.from(event.dataTransfer.files)
    addFiles(files)
  }
}

const addFiles = (files: File[]) => {
  // Filtrar apenas PDFs
  const pdfFiles = files.filter(file => file.type === 'application/pdf')
  
  if (pdfFiles.length !== files.length) {
    errorMessage.value = 'Apenas arquivos PDF são aceitos'
    return
  }
  
  // Validar quantidade total
  if (selectedFiles.value.length + pdfFiles.length > 5) {
    errorMessage.value = 'Máximo de 5 arquivos permitidos'
    return
  }
  
  // Validar tamanho
  const maxSize = 5 * 1024 * 1024 // 5MB
  const oversizedFiles = pdfFiles.filter(file => file.size > maxSize)
  if (oversizedFiles.length > 0) {
    errorMessage.value = `Arquivos muito grandes: ${oversizedFiles.map(f => f.name).join(', ')}`
    return
  }
  
  selectedFiles.value = [...selectedFiles.value, ...pdfFiles]
  errorMessage.value = ''
}

const removeFile = (index: number) => {
  selectedFiles.value.splice(index, 1)
}

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const analyzeCvs = async () => {
  if (selectedFiles.value.length < 2) {
    errorMessage.value = 'Selecione pelo menos 2 arquivos'
    return
  }

  isLoading.value = true
  errorMessage.value = ''
  analysisResults.value = null

  try {
    const formData = new FormData()
    selectedFiles.value.forEach(file => {
      formData.append('files[]', file)
    })

    const response = await axios.post('/analyze-cvs', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Accept': 'application/json'
      },
      timeout: 60000 // 60 segundos de timeout
    })

    // Verificar se a resposta contém dados válidos
    if (response.data && response.data.candidatos) {
      analysisResults.value = response.data
    } else {
      throw new Error('Resposta inválida do servidor')
    }
  } catch (error: any) {
    console.error('Erro ao analisar CVs:', error)
    
    let errorMsg = 'Erro ao analisar os currículos.'
    
    if (error.response) {
      // Erro de resposta do servidor
      if (error.response.status === 500) {
        errorMsg = 'Erro interno do servidor. Tente novamente em alguns minutos.'
      } else if (error.response.status === 422) {
        errorMsg = 'Arquivos inválidos. Verifique se são PDFs válidos e menores que 5MB.'
      } else if (error.response.data?.message) {
        errorMsg = error.response.data.message
      }
    } else if (error.request) {
      // Erro de rede
      errorMsg = 'Erro de conexão. Verifique se o backend está rodando.'
    } else if (error.message) {
      // Outros erros
      errorMsg = error.message
    }
    
    errorMessage.value = errorMsg
  } finally {
    isLoading.value = false
  }
}

const getUniqueSkillsForCandidate = (candidateName: string): string[] => {
  if (!analysisResults.value?.comparacao?.skills_unicas) return []
  return analysisResults.value.comparacao.skills_unicas[candidateName] || []
}

const getTotalUniqueSkills = (): number => {
  if (!analysisResults.value?.comparacao?.skills_unicas) return 0
  const allUniqueSkills = Object.values(analysisResults.value.comparacao.skills_unicas).flat()
  return new Set(allUniqueSkills).size
}

const getHighestScore = (): number => {
  if (!analysisResults.value?.candidatos) return 0
  return Math.max(...analysisResults.value.candidatos.map(c => c.pontuacao))
}

const sortedCandidates = computed(() => {
  if (!analysisResults.value?.candidatos) return []
  // Ordena por pontuação decrescente
  return [...analysisResults.value.candidatos].sort((a, b) => b.pontuacao - a.pontuacao)
})
</script>

<style scoped>
.cv-analyzer-outer {
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.cv-analyzer-flex {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  align-items: stretch;
}

.upload-card, .results-card {
  width: 100%;
  min-width: 320px;
  max-width: 100%;
  margin: 0 auto;
}

/* Card Styles */
.card {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.card-description {
  color: #64748b;
  margin: 0;
  font-size: 0.875rem;
}

.card-content {
  padding: 1.5rem;
}

/* Upload Area */
.upload-area {
  border: 2px dashed #cbd5e1;
  border-radius: 0.5rem;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background: #f8fafc;
}

.upload-area:hover {
  border-color: #3b82f6;
  background: #eff6ff;
}

/* Button Styles */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.875rem;
  gap: 0.5rem;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-success {
  background-color: #059669;
  color: white;
}

.btn-success:hover {
  background-color: #047857;
}

.btn-success:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

.btn-danger {
  background-color: #dc2626;
  color: white;
  padding: 0.25rem;
  border-radius: 0.25rem;
}

.btn-danger:hover {
  background-color: #b91c1c;
}

.btn-full {
  width: 100%;
}

/* File List */
.file-list {
  margin-top: 1.5rem;
}

.file-item {
  display: flex;
  align-items: center;
  justify-content: between;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.375rem;
  margin-bottom: 0.5rem;
  background: white;
}

.file-info {
  display: flex;
  align-items: center;
  flex: 1;
  gap: 0.5rem;
}

.file-name {
  font-weight: 500;
  color: #374151;
}

.file-size {
  color: #6b7280;
  font-size: 0.875rem;
}

.icon {
  width: 1.25rem;
  height: 1.25rem;
}

/* Skills */
.skill-tag {
  display: inline-block;
  background-color: #e0f2fe;
  color: #0c4a6e;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  margin: 0.125rem;
}

.skill-tag.common {
  background-color: #dcfce7;
  color: #166534;
}

.skill-tag.unique {
  background-color: #fef3c7;
  color: #92400e;
}

.skill-tag.small {
  font-size: 0.75rem;
  padding: 0.125rem 0.375rem;
}

.skill-frequency {
  font-weight: 400;
  opacity: 0.8;
}

.skills-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
}

/* Tabela Comparativa */
.table-container {
  overflow-x: auto;
  border-radius: 0.5rem;
  border: 1px solid #e2e8f0;
}

.comparison-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.comparison-table th {
  background-color: #f8fafc;
  padding: 0.75rem 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 1px solid #e2e8f0;
  font-size: 0.875rem;
}

.comparison-table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: top;
}

.candidate-name {
  font-weight: 600;
  color: #1e293b;
}

.candidate-email {
  font-family: monospace;
  font-size: 0.875rem;
  color: #64748b;
}

.candidate-score {
  text-align: center;
}

.score-badge {
  display: inline-block;
  background-color: #3b82f6;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  font-weight: 600;
  font-size: 0.875rem;
}

.skills-count {
  text-align: center;
  font-weight: 600;
  color: #059669;
}

.skills-cell {
  max-width: 200px;
}

.skills-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
}

.more-skills {
  font-size: 0.75rem;
  color: #6b7280;
  font-style: italic;
}

.no-skills {
  font-size: 0.75rem;
  color: #9ca3af;
  font-style: italic;
}

/* Stats Summary */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  padding: 1rem;
  text-align: center;
}

.stat-value {
  font-size: 1.875rem;
  font-weight: 700;
  color: #3b82f6;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

/* Candidate Cards */
.candidate-grid {
  display: grid;
  gap: 1rem;
}

.candidate-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  padding: 1.5rem;
}

.candidate-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.candidate-info h4 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
}

.candidate-info p {
  margin: 0.25rem 0 0 0;
  color: #64748b;
  font-family: monospace;
  font-size: 0.875rem;
}

.score {
  text-align: center;
}

.score-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #3b82f6;
  margin: 0;
}

.score-label {
  font-size: 0.75rem;
  color: #6b7280;
  margin: 0;
}

.skills-section h5 {
  margin: 0 0 0.5rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

/* Spinner */
.spinner {
  width: 1rem;
  height: 1rem;
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 0.5rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Error */
.error {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 0.5rem;
  padding: 1rem;
  margin-top: 1rem;
}

.error-icon {
  width: 1.25rem;
  height: 1.25rem;
  color: #dc2626;
  flex-shrink: 0;
}

.error-content h3 {
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #dc2626;
}

.error-content p {
  margin: 0;
  font-size: 0.875rem;
  color: #991b1b;
}

/* Hidden */
.hidden {
  display: none;
}

/* Responsive */
@media (max-width: 768px) {
  .cv-analyzer-outer {
    padding: 1rem 0.5rem;
  }

  .card-header, .card-content {
    padding: 1rem;
  }

  .comparison-table th,
  .comparison-table td {
    padding: 0.5rem;
    font-size: 0.875rem;
  }
  
  .skills-cell {
    max-width: 120px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .candidate-header {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>
