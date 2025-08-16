<template>
  <div class="cv-optimizer-container">
    <!-- Header -->
    <div class="header-section">
      <h1 class="title">🤖</h1>
      <h2 class="title" style="color: aliceblue; font-weight: bold;">Otimizador de CV</h2>
    </div>

    <!-- Upload Section -->
    <div v-if="!currentCV" class="upload-section">
      <div class="upload-card">
        <div class="upload-area" @click="triggerFileInput" @dragover.prevent @drop.prevent="handleDrop">
          <input
            ref="fileInput"
            type="file"
            @change="handleFileSelect"
            accept=".pdf,.doc,.docx"
            class="hidden"
          >
          <div class="upload-content">
            <div class="upload-icon">📄</div>
            <h3>Faça upload do seu CV</h3>
            <p>Arraste e solte ou clique para selecionar</p>
            <p class="file-types">PDF, DOC, DOCX • Máx 10MB</p>
          </div>
        </div>
        
        <div v-if="uploadProgress" class="progress-bar">
          <div class="progress-fill" :style="{ width: uploadProgress + '%' }"></div>
        </div>
        
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
      </div>
    </div>

    <!-- CV Editor Section -->
    <div v-else class="editor-section">
      <div class="editor-header">
        <h2>{{ currentCV.dados_pessoais?.nome || 'Seu CV' }}</h2>
        <div class="header-actions">
          <div class="ia-status" :class="{ ok: iaAvailable === true, warn: iaAvailable === false }" v-if="iaAvailable !== null">
            <span v-if="iaAvailable">🟢 IA disponível</span>
            <span v-else>🟡 Modo básico</span>
          </div>
          <div class="setor-selector">
            <label for="setor">Setor:</label>
            <select id="setor" v-model="selectedSetor" class="setor-select">
              <option value="">Geral</option>
              <option value="tecnologia">Tecnologia</option>
              <option value="saude">Saúde</option>
              <option value="educacao">Educação</option>
              <option value="financeiro">Financeiro</option>
              <option value="marketing">Marketing</option>
              <option value="vendas">Vendas</option>
              <option value="recursos-humanos">Recursos Humanos</option>
              <option value="engenharia">Engenharia</option>
              <option value="design">Design</option>
              <option value="juridico">Jurídico</option>
            </select>
          </div>
          <button 
            @click="optimizeWithAI" 
            :disabled="isOptimizing"
            class="btn btn-primary"
          >
            {{ isOptimizing ? 'Otimizando...' : '🚀 Otimizar com IA' }}
          </button>
          <button @click="showTemplateSelector = true" class="btn btn-secondary">
            📑 Escolher Template
          </button>
          <button @click="generatePDF" class="btn btn-success">
            📄 Gerar PDF
          </button>
        </div>
      </div>

      <!-- Editor Tabs -->
      <div class="editor-tabs">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="['tab', { active: activeTab === tab.id }]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab Content -->
      <div class="tab-content">
        <!-- Dados Pessoais -->
        <div v-if="activeTab === 'pessoais'" class="tab-panel">
          <h3>Dados Pessoais</h3>
          <div class="form-grid">
            <div class="form-group">
              <label>Nome Completo</label>
              <input 
                v-model="currentCV.dados_pessoais.nome" 
                type="text" 
                class="form-input"
              >
            </div>
            <div class="form-group">
              <label>Email</label>
              <input 
                v-model="currentCV.dados_pessoais.email" 
                type="email" 
                class="form-input"
              >
            </div>
            <div class="form-group">
              <label>Telefone</label>
              <input 
                v-model="currentCV.dados_pessoais.telefone" 
                type="tel" 
                class="form-input"
              >
            </div>
            <div class="form-group">
              <label>LinkedIn</label>
              <input 
                v-model="currentCV.dados_pessoais.linkedin" 
                type="url" 
                class="form-input"
              >
            </div>
          </div>
        </div>

        <!-- Resumo -->
        <div v-if="activeTab === 'resumo'" class="tab-panel">
          <h3>Resumo Profissional</h3>
          <div class="form-group">
            <label>Descreva seu perfil profissional</label>
            <textarea 
              v-model="currentCV.resumo_pessoal" 
              class="form-textarea"
              rows="6"
              placeholder="Ex: Profissional experiente em..."
            ></textarea>
          </div>
        </div>

        <!-- Experiências -->
        <div v-if="activeTab === 'experiencias'" class="tab-panel">
          <div class="section-header">
            <h3>Experiência Profissional</h3>
            <button @click="addExperience" class="btn btn-small">+ Adicionar</button>
          </div>
          
          <div v-for="(exp, index) in currentCV.experiencias" :key="index" class="experience-item">
            <div class="item-header">
              <span class="item-number">{{ index + 1 }}</span>
              <button @click="removeExperience(index)" class="btn-remove">×</button>
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label>Cargo</label>
                <input v-model="exp.cargo" type="text" class="form-input">
              </div>
              <div class="form-group">
                <label>Empresa</label>
                <input v-model="exp.empresa" type="text" class="form-input">
              </div>
              <div class="form-group">
                <label>Período</label>
                <input v-model="exp.periodo" type="text" class="form-input" placeholder="Ex: Jan 2020 - Dez 2022">
              </div>
            </div>
            
            <div class="form-group">
              <label>Descrição</label>
              <textarea v-model="exp.descricao" class="form-textarea" rows="3"></textarea>
            </div>
            
            <div class="form-group">
              <label>Principais Conquistas</label>
              <div v-for="(conquista, cIndex) in exp.conquistas" :key="cIndex" class="achievement-item">
                <input v-model="exp.conquistas[cIndex]" type="text" class="form-input">
                <button @click="removeAchievement(index, cIndex)" class="btn-remove-small">×</button>
              </div>
              <button @click="addAchievement(index)" class="btn btn-small">+ Conquista</button>
            </div>
          </div>
        </div>

        <!-- Skills -->
        <div v-if="activeTab === 'skills'" class="tab-panel">
          <div class="section-header">
            <h3>Competências</h3>
            <button @click="addSkillCategory" class="btn btn-small">+ Categoria</button>
          </div>
          
          <div v-for="(skillGroup, index) in currentCV.skills" :key="index" class="skill-category">
            <div class="category-header">
              <input 
                v-model="skillGroup.categoria" 
                type="text" 
                class="category-input"
                placeholder="Ex: Tecnologias"
              >
              <button @click="removeSkillCategory(index)" class="btn-remove">×</button>
            </div>
            
            <div class="skills-list">
              <div v-for="(skill, sIndex) in skillGroup.habilidades" :key="sIndex" class="skill-item">
                <input v-model="skillGroup.habilidades[sIndex]" type="text" class="skill-input">
                <button @click="removeSkill(index, sIndex)" class="btn-remove-small">×</button>
              </div>
              <button @click="addSkill(index)" class="btn btn-small">+ Skill</button>
            </div>
          </div>
        </div>

        <!-- Formação -->
        <div v-if="activeTab === 'formacao'" class="tab-panel">
          <div class="section-header">
            <h3>Formação Acadêmica</h3>
            <button @click="addEducation" class="btn btn-small">+ Adicionar</button>
          </div>
          
          <div v-for="(edu, index) in currentCV.formacao" :key="index" class="education-item">
            <div class="item-header">
              <span class="item-number">{{ index + 1 }}</span>
              <button @click="removeEducation(index)" class="btn-remove">×</button>
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label>Curso</label>
                <input v-model="edu.curso" type="text" class="form-input">
              </div>
              <div class="form-group">
                <label>Instituição</label>
                <input v-model="edu.instituicao" type="text" class="form-input">
              </div>
              <div class="form-group">
                <label>Período</label>
                <input v-model="edu.periodo" type="text" class="form-input" placeholder="Ex: 2018 - 2022">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="save-section">
        <button @click="() => saveCV()" :disabled="isSaving" class="btn btn-primary btn-large">
          {{ isSaving ? 'Salvando...' : 'Salvar Alterações' }}
        </button>
      </div>
    </div>

    <!-- Template Selector Modal -->
    <div v-if="showTemplateSelector" class="modal-overlay" @click="showTemplateSelector = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Escolha um Template</h3>
          <button @click="showTemplateSelector = false" class="btn-close">×</button>
        </div>
        
        <div class="templates-grid">
          <div 
            v-for="template in availableTemplates" 
            :key="template.id"
            @click="selectTemplate(template.id)"
            :class="['template-card', { selected: selectedTemplate === template.id }]"
          >
            <div class="template-preview">
              <img :src="template.preview" :alt="template.nome" />
            </div>
            <div class="template-info">
              <h4>{{ template.nome }}</h4>
              <p>{{ template.descricao }}</p>
            </div>
          </div>
        </div>
        
        <div class="modal-actions">
          <button @click="applyTemplate" class="btn btn-primary">Aplicar Template</button>
          <button @click="showTemplateSelector = false" class="btn btn-secondary">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'

// Interfaces
interface Template {
  id: string
  nome: string
  descricao: string
  preview: string
}

// State
const currentCV = ref<any>(null)
const activeTab = ref('pessoais')
const uploadProgress = ref(0)
const errorMessage = ref('')
const isOptimizing = ref(false)
const isSaving = ref(false)
const showTemplateSelector = ref(false)
const selectedTemplate = ref('classico')
const selectedSetor = ref('')
const availableTemplates = ref<Template[]>([])
const fileInput = ref<HTMLInputElement>()
const iaAvailable = ref<boolean|null>(null)

// Tabs configuration
const tabs = [
  { id: 'pessoais', label: 'Dados Pessoais' },
  { id: 'resumo', label: 'Resumo' },
  { id: 'experiencias', label: 'Experiências' },
  { id: 'skills', label: 'Competências' },
  { id: 'formacao', label: 'Formação' }
]

// Methods
const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    uploadFile(target.files[0])
  }
}

const handleDrop = (event: DragEvent) => {
  if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
    uploadFile(event.dataTransfer.files[0])
  }
}

const uploadFile = async (file: File) => {
  try {
    errorMessage.value = ''
    uploadProgress.value = 0
    
    const formData = new FormData()
    formData.append('cv_file', file)
    
    const response = await api.post('/candidatos/cv-optimizer/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        }
      }
    })
    
    if (response.data.cv_id) {
      // Usar dados extraídos diretamente do upload, depois carregar CV completo
      if (response.data.dados) {
        currentCV.value = {
          id: response.data.cv_id,
          ...initializeCV(response.data.dados)
        }
      }
      // Carregar CV completo do banco para ter todos os campos
      await loadCV(response.data.cv_id)
    }
    
  } catch (error: any) {
    console.error('Erro no upload:', error)
    errorMessage.value = error.response?.data?.message || 'Erro ao fazer upload do CV'
    uploadProgress.value = 0
  }
}

const initializeCV = (dados: any) => {
  return {
    dados_pessoais: dados.dados_pessoais || {
      nome: '',
      email: '',
      telefone: '',
      linkedin: ''
    },
    resumo_pessoal: dados.resumo_pessoal || '',
    experiencias: dados.experiencias || [],
    skills: dados.skills || [],
    formacao: dados.formacao || [],
    template_escolhido: 'classico'
  }
}

const loadCV = async (cvId: number) => {
  try {
    const response = await api.get(`/candidatos/cv-optimizer/${cvId}`)
    const cvData = response.data.cv
    
    // Garantir que todos os campos tenham a estrutura correta
    currentCV.value = {
      ...cvData,
      dados_pessoais: cvData.dados_pessoais || {
        nome: '',
        email: '',
        telefone: '',
        linkedin: ''
      },
      resumo_pessoal: cvData.resumo_pessoal || '',
      experiencias: cvData.experiencias || [],
      skills: cvData.skills || [],
      formacao: cvData.formacao || [],
      template_escolhido: cvData.template_escolhido || 'classico'
    }
    
    selectedTemplate.value = currentCV.value.template_escolhido
    
  } catch (error) {
    console.error('Erro ao carregar CV:', error)
  }
}

const optimizeWithAI = async () => {
  if (!currentCV.value) return
  
  try {
    isOptimizing.value = true
    errorMessage.value = ''
    
    const response = await api.post(`/candidatos/cv-optimizer/${currentCV.value.id}/otimizar`, {
      setor: selectedSetor.value || null
    })
    
    console.log('Resposta da otimização:', response.data)
    
    // Atualizar dados com versão otimizada - substituir completamente
    if (response.data.dados_otimizados) {
      console.log('Dados antes da atualização:', JSON.parse(JSON.stringify(currentCV.value)))
      Object.assign(currentCV.value, response.data.dados_otimizados)
      console.log('Dados depois da atualização:', JSON.parse(JSON.stringify(currentCV.value)))
      
      // Forçar reatividade do Vue
      currentCV.value = { ...currentCV.value }
      
      // Salvar automaticamente após otimização
      await saveCV(true)
      
      // Mostrar mensagem de sucesso diferente para fallback
      if (response.data.fallback) {
        alert(`⚠️ CV otimizado com sucesso (modo básico)${selectedSetor.value ? ` para o setor de ${selectedSetor.value}` : ''}!\n\nAs melhorias incluem:\n• Campos preenchidos automaticamente\n• Estrutura otimizada\n• Nota: A IA não estava disponível, foi aplicada otimização básica`)
      } else {
        alert(`✅ CV otimizado com sucesso${selectedSetor.value ? ` para o setor de ${selectedSetor.value}` : ''}!\n\nAs melhorias incluem:\n• Textos reescritos para maior impacto\n• Estrutura otimizada\n• Linguagem profissional adaptada`)
      }
    }
    
  } catch (error: any) {
    console.error('Erro na otimização:', error)
    errorMessage.value = error.response?.data?.message || 'Erro ao otimizar CV'
  } finally {
    isOptimizing.value = false
  }
}

const saveCV = async (silent = false) => {
  if (!currentCV.value) return
  
  try {
    isSaving.value = true
    
    await api.put(`/candidatos/cv-optimizer/${currentCV.value.id}`, {
      dados_pessoais: currentCV.value.dados_pessoais,
      resumo_pessoal: currentCV.value.resumo_pessoal,
      experiencias: currentCV.value.experiencias,
      skills: currentCV.value.skills,
      formacao: currentCV.value.formacao,
      template_escolhido: selectedTemplate.value
    })
    
    if (!silent) {
      alert('CV salvo com sucesso!')
    }
    
  } catch (error: any) {
    console.error('Erro ao salvar:', error)
    if (!silent) {
      errorMessage.value = error.response?.data?.message || 'Erro ao salvar CV'
    }
  } finally {
    isSaving.value = false
  }
}

const generatePDF = async () => {
  if (!currentCV.value) return
  
  try {
    const response = await api.post(`/candidatos/cv-optimizer/${currentCV.value.id}/pdf`, {
      template: selectedTemplate.value
    })
    
    // Abrir PDF em nova aba
    window.open(response.data.pdf_url, '_blank')
    
  } catch (error: any) {
    console.error('Erro ao gerar PDF:', error)
    errorMessage.value = error.response?.data?.message || 'Erro ao gerar PDF'
  }
}

// Experience management
const addExperience = () => {
  if (!currentCV.value.experiencias) {
    currentCV.value.experiencias = []
  }
  currentCV.value.experiencias.push({
    cargo: '',
    empresa: '',
    periodo: '',
    descricao: '',
    conquistas: ['']
  })
}

const removeExperience = (index: number) => {
  currentCV.value.experiencias.splice(index, 1)
}

const addAchievement = (expIndex: number) => {
  currentCV.value.experiencias[expIndex].conquistas.push('')
}

const removeAchievement = (expIndex: number, achievementIndex: number) => {
  currentCV.value.experiencias[expIndex].conquistas.splice(achievementIndex, 1)
}

// Skills management
const addSkillCategory = () => {
  if (!currentCV.value.skills) {
    currentCV.value.skills = []
  }
  currentCV.value.skills.push({
    categoria: '',
    habilidades: ['']
  })
}

const removeSkillCategory = (index: number) => {
  currentCV.value.skills.splice(index, 1)
}

const addSkill = (categoryIndex: number) => {
  currentCV.value.skills[categoryIndex].habilidades.push('')
}

const removeSkill = (categoryIndex: number, skillIndex: number) => {
  currentCV.value.skills[categoryIndex].habilidades.splice(skillIndex, 1)
}

// Education management
const addEducation = () => {
  if (!currentCV.value.formacao) {
    currentCV.value.formacao = []
  }
  currentCV.value.formacao.push({
    curso: '',
    instituicao: '',
    periodo: ''
  })
}

const removeEducation = (index: number) => {
  currentCV.value.formacao.splice(index, 1)
}

// Template management
const loadTemplates = async () => {
  try {
    const response = await api.get('/candidatos/cv-optimizer/templates/listar')
    availableTemplates.value = response.data.templates
  } catch (error) {
    console.error('Erro ao carregar templates:', error)
  }
}

const loadIaStatus = async () => {
  try {
    const response = await api.get('/candidatos/cv-optimizer/ia/status')
    iaAvailable.value = !!response.data.ia_disponivel
  } catch (e) {
    iaAvailable.value = false
  }
}

const selectTemplate = (templateId: string) => {
  selectedTemplate.value = templateId
}

const applyTemplate = () => {
  showTemplateSelector.value = false
  if (currentCV.value) {
    currentCV.value.template_escolhido = selectedTemplate.value
  }
}

// Initialize
onMounted(() => {
  loadTemplates()
  loadIaStatus()
})
</script>

<style scoped>
.cv-optimizer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.header-section {
  text-align: center;
  margin-bottom: 40px;
}

.title {
  font-size: 2.5rem;
  color: #2c3e50;
  margin-bottom: 10px;
}

.subtitle {
  font-size: 1.1rem;
  color: #7f8c8d;
  max-width: 600px;
  margin: 0 auto;
}

.upload-section {
  margin-bottom: 40px;
}

.upload-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.upload-area {
  padding: 60px 20px;
  border: 3px dashed #3498db;
  margin: 20px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #f8f9fa;
}

.upload-area:hover {
  border-color: #2980b9;
  background: #e3f2fd;
}

.upload-content {
  text-align: center;
}

.upload-icon {
  font-size: 4rem;
  margin-bottom: 20px;
}

.upload-content h3 {
  color: #2c3e50;
  margin-bottom: 10px;
}

.file-types {
  color: #7f8c8d;
  font-size: 0.9rem;
}

.hidden {
  display: none;
}

.progress-bar {
  height: 4px;
  background: #ecf0f1;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #3498db;
  transition: width 0.3s ease;
}

.error-message {
  color: #e74c3c;
  text-align: center;
  padding: 15px;
  background: #ffeaea;
  margin: 20px;
  border-radius: 8px;
}

.editor-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 30px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.editor-header h2 {
  margin: 0;
  font-size: 1.8rem;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.ia-status {
  align-self: flex-start;
  padding: 6px 10px;
  border-radius: 9999px;
  font-size: 0.85rem;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255,255,255,0.3);
}
.ia-status.ok { color: #d1fae5; border-color: #10b981; }
.ia-status.warn { color: #fef3c7; border-color: #f59e0b; }

.setor-selector {
  display: flex;
  flex-direction: column;
  gap: 5px;
  margin-right: 15px;
}

.setor-selector label {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
}

.setor-select {
  padding: 8px 12px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-size: 0.9rem;
  min-width: 150px;
}

.setor-select:focus {
  outline: none;
  border-color: rgba(255, 255, 255, 0.5);
  background: rgba(255, 255, 255, 0.2);
}

.setor-select option {
  background: #2c3e50;
  color: white;
}

.editor-tabs {
  display: flex;
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.tab {
  padding: 15px 25px;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 1rem;
  color: #6c757d;
  border-bottom: 3px solid transparent;
  transition: all 0.3s ease;
}

.tab:hover {
  background: #e9ecef;
  color: #495057;
}

.tab.active {
  color: #3498db;
  border-bottom-color: #3498db;
  background: white;
}

.tab-content {
  padding: 30px;
}

.tab-panel {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.section-header h3 {
  margin: 0;
  color: #2c3e50;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 25px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 8px;
  font-weight: 600;
  color: #34495e;
}

.form-input,
.form-textarea {
  padding: 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.experience-item,
.education-item {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 20px;
  border: 1px solid #e9ecef;
}

.skill-category {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 15px;
  border: 1px solid #e9ecef;
}

.item-header,
.category-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.item-number {
  background: #3498db;
  color: white;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.category-input {
  font-size: 1.1rem;
  font-weight: 600;
  border: none;
  background: none;
  color: #2c3e50;
  flex: 1;
  padding: 8px;
}

.skills-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.skill-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.skill-input {
  padding: 8px 12px;
  border: 1px solid #dee2e6;
  border-radius: 20px;
  font-size: 0.9rem;
  min-width: 120px;
}

.achievement-item {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #3498db;
  color: white;
}

.btn-primary:hover {
  background: #2980b9;
  transform: translateY(-2px);
}

.btn-secondary {
  background: #95a5a6;
  color: white;
}

.btn-secondary:hover {
  background: #7f8c8d;
}

.btn-success {
  background: #27ae60;
  color: white;
}

.btn-success:hover {
  background: #219a52;
}

.btn-small {
  padding: 6px 12px;
  font-size: 0.85rem;
}

.btn-large {
  padding: 15px 30px;
  font-size: 1.1rem;
}

.btn-remove,
.btn-remove-small {
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.btn-remove-small {
  width: 24px;
  height: 24px;
  font-size: 1rem;
}

.btn-remove:hover,
.btn-remove-small:hover {
  background: #c0392b;
}

.save-section {
  text-align: center;
  padding: 30px;
  background: #f8f9fa;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 25px;
  border-bottom: 1px solid #dee2e6;
}

.modal-header h3 {
  margin: 0;
  color: #2c3e50;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6c757d;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close:hover {
  background: #f8f9fa;
}

.templates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  padding: 25px;
}

.template-card {
  border: 2px solid #e9ecef;
  border-radius: 12px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.template-card:hover {
  border-color: #3498db;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.template-card.selected {
  border-color: #3498db;
  background: #e3f2fd;
}

.template-preview {
  height: 150px;
  background: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.template-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.template-info h4 {
  margin: 0 0 8px 0;
  color: #2c3e50;
}

.template-info p {
  margin: 0;
  color: #7f8c8d;
  font-size: 0.9rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  padding: 25px;
  background: #f8f9fa;
}

@media (max-width: 768px) {
  .editor-header {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }
  
  .header-actions {
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .setor-selector {
    margin-right: 0;
    margin-bottom: 10px;
  }
  
  .editor-tabs {
    flex-wrap: wrap;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .templates-grid {
    grid-template-columns: 1fr;
  }
}
</style>
