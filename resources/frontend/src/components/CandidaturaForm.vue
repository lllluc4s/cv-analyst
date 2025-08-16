<template>
  <div class="candidatura-form-container">
    <!-- Estado de carregamento -->
    <div v-if="isLoading" class="flex justify-center items-center py-10">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      <p class="ml-3 text-gray-600">Verificando autenticação...</p>
    </div>
    
    <!-- Formulário de candidatura sempre visível -->
    <div v-else class="candidatura-form">
      <!-- Login social opcional no topo -->
      <div v-if="!isAuthenticated" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">Login Social (Opcional)</h3>
            <p class="text-sm text-blue-700">Para facilitar o preenchimento, você pode fazer login com uma das opções abaixo:</p>
            <div class="mt-3">
              <SocialLogin />
            </div>
          </div>
        </div>
      </div>
      
      <!-- Mensagens de status -->
      <div v-if="submissionStatus === 'success'" class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded shadow-sm">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-green-700">Candidatura enviada com sucesso! Você receberá um email de confirmação em breve.</p>
          </div>
        </div>
      </div>
      
      <div v-if="submissionStatus === 'error'" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded shadow-sm">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700">{{ errorMessage || 'Ocorreu um erro ao enviar sua candidatura. Por favor, tente novamente.' }}</p>
          </div>
        </div>
      </div>
      
      <form @submit.prevent="submitCandidatura" v-if="submissionStatus !== 'success'" class="space-y-6">
        <!-- Dados do usuário autenticado (se logado) -->
        <div v-if="isAuthenticated" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <span class="font-medium text-gray-700">Logado como:</span>
              <span class="ml-2 text-gray-900">{{ userData.name }}</span>
              <span class="ml-1 text-gray-500">({{ userData.email }})</span>
              <span v-if="userData.provider" class="ml-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                  'bg-red-100 text-red-800': userData.provider === 'google',
                  'bg-gray-100 text-gray-800': userData.provider === 'github',
                  'bg-blue-100 text-blue-800': userData.provider === 'linkedin'
                }">
                  <i :class="getProviderIcon" class="mr-1"></i>
                  {{ userData.provider }}
                </span>
              </span>
            </div>
            <!-- Botão de logout -->
            <button 
              @click="logout" 
              type="button"
              class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs leading-4 font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <i class="fas fa-sign-out-alt mr-1"></i>
              Sair
            </button>
          </div>
        </div>
        
        <!-- Campos de dados pessoais (visíveis se não estiver autenticado) -->
        <div v-if="!isAuthenticated" class="space-y-4">
          <div>
            <label for="nome" class="block text-sm font-medium text-gray-700">Nome completo *</label>
            <div class="mt-1">
              <input 
                type="text" 
                id="nome" 
                v-model="formData.nome" 
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                required
                placeholder="Seu nome completo"
              >
            </div>
          </div>
          
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
            <div class="mt-1">
              <input 
                type="email" 
                id="email" 
                v-model="formData.email" 
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                required
                placeholder="seu.email@exemplo.com"
              >
            </div>
          </div>
          
          <div>
            <label for="linkedin" class="block text-sm font-medium text-gray-700">LinkedIn (opcional)</label>
            <div class="mt-1">
              <input 
                type="url" 
                id="linkedin" 
                v-model="formData.linkedin" 
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                placeholder="https://linkedin.com/in/seuusuario"
              >
            </div>
          </div>
        </div>
        
        <!-- Campos do formulário -->
        <div>
          <label for="telefone" class="block text-sm font-medium text-gray-700">Telefone *</label>
          <div class="mt-1">
            <input 
              type="tel" 
              id="telefone" 
              v-model="formData.telefone" 
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
              required
              placeholder="+351 999 999 999"
            >
          </div>
        </div>
        
        <div>
          <label for="cv_file" class="block text-sm font-medium text-gray-700">Currículo (PDF, DOC, DOCX) *</label>
          <div class="mt-1">
            <input 
              type="file" 
              id="cv_file" 
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" 
              @change="handleFileUpload" 
              required
              accept=".pdf,.doc,.docx"
            >
          </div>
          <p class="mt-1 text-xs text-gray-500">Tamanho máximo de 5MB</p>
        </div>
        
        <div class="flex items-start">
          <div class="flex items-center h-5">
            <input 
              id="rgpd" 
              v-model="formData.rgpd" 
              type="checkbox" 
              class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
              required
            >
          </div>
          <div class="ml-3 text-sm">
            <label for="rgpd" class="font-medium text-gray-700">Concordo com o processamento dos meus dados para fins de recrutamento</label>
          </div>
        </div>
        
        <RecaptchaDisclaimer />
        
        <div>
          <button 
            type="submit" 
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            :disabled="isSubmitting"
          >
            <span v-if="isSubmitting" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Enviando...
            </span>
            <span v-else>Enviar candidatura</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import SocialLogin from '@/components/auth/SocialLogin.vue';
import RecaptchaDisclaimer from '@/components/RecaptchaDisclaimer.vue';
import axios from 'axios';
import ApiService from '../services/ApiService';

export default defineComponent({
  name: 'CandidaturaForm',
  components: {
    SocialLogin,
    RecaptchaDisclaimer
  },
  props: {
    oportunidadeId: {
      type: [Number, String],
      required: true
    },
    recaptchaToken: {
      type: String,
      default: ''
    }
  },
  setup(props) {
    const isAuthenticated = ref(false); // Inicialmente sempre falso para garantir verificação
    const isLoading = ref(true); // Estado de carregamento
    const userData = ref({
      name: '',
      email: '',
      provider: '',
      provider_id: '',
      avatar: '',
      profile_url: ''
    });
    const formData = ref({
      oportunidade_id: props.oportunidadeId,
      nome: '',
      email: '',
      linkedin: '',
      telefone: '',
      rgpd: false,
      recaptcha_token: props.recaptchaToken
    });
    const cv_file = ref<File | null>(null);
    const isSubmitting = ref(false);
    const submissionStatus = ref('');
    const errorMessage = ref('');
    
    // Verificar autenticação e carregar dados do usuário
    const checkAuth = async () => {
      try {
        console.log('🚀 CandidaturaForm: Iniciando verificação de autenticação...');
        
        // Verificar se há token na URL (retorno do login social)
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('_auth_token') || urlParams.get('token');
        
        if (token) {
          console.log('🔑 Token encontrado na URL:', token.substring(0, 10) + '...');
          
          // Verificar se já existe o mesmo token no localStorage
          const existingToken = localStorage.getItem('auth_token');
          if (existingToken === token) {
            console.log('ℹ️ Token já está salvo no localStorage, ignorando');
          } else {
            console.log('🔑 Armazenando novo token...');
            // Armazenar token e remover da URL
            ApiService.setAuthToken(token);
            
            // Verificar novamente se o token foi salvo
            const checkToken = localStorage.getItem('auth_token');
            console.log('🔍 Verificação após salvar:', checkToken ? 'Token salvo' : 'Token NÃO salvo!');
          }
          
          // Limpar URL
          const url = new URL(window.location.href);
          url.searchParams.delete('_auth_token');
          url.searchParams.delete('token');
          url.searchParams.delete('auth');
          window.history.replaceState({}, document.title, url.toString());
          console.log('🧹 URL limpa, token removido dos parâmetros');
        } else {
          console.log('ℹ️ Nenhum token encontrado na URL');
        }
        
        // Verificar se está autenticado
        console.log('🔍 Verificando status de autenticação via API...');
        const isAuth = await ApiService.checkAuth();
        console.log('🔐 Status de autenticação:', isAuth ? 'AUTENTICADO' : 'NÃO AUTENTICADO');
        
        if (isAuth) {
          isAuthenticated.value = true;
          console.log('✅ Usuário autenticado, carregando perfil...');
          // Obter dados do usuário
          const profileData = await ApiService.getUserProfile();
          if (profileData.user) {
            userData.value = profileData.user;
            console.log('👤 Dados do usuário carregados:', profileData.user);
          }
        } else {
          isAuthenticated.value = false;
          console.log('❌ Usuário não autenticado - mostrando tela de login');
        }
      } catch (error) {
        isAuthenticated.value = false;
        console.error('💥 Erro ao verificar autenticação:', error);
      } finally {
        isLoading.value = false; // Parar carregamento
        console.log('🏁 Verificação de autenticação concluída');
      }
    };
    
    // Lidar com upload de arquivo
    const handleFileUpload = (event: Event) => {
      const input = event.target as HTMLInputElement;
      if (input.files && input.files.length > 0) {
        cv_file.value = input.files[0];
      }
    };
    
    // Enviar candidatura
    const submitCandidatura = async () => {
      if (!cv_file.value) {
        alert('Por favor, selecione um arquivo de currículo.');
        return;
      }
      
      if (!formData.value.rgpd) {
        alert('Por favor, aceite os termos de privacidade.');
        return;
      }

      // Validar campos obrigatórios se não estiver autenticado
      if (!isAuthenticated.value) {
        if (!formData.value.nome.trim()) {
          alert('Por favor, preencha seu nome.');
          return;
        }
        if (!formData.value.email.trim()) {
          alert('Por favor, preencha seu email.');
          return;
        }
      }
      
      isSubmitting.value = true;
      submissionStatus.value = '';
      
      try {
        const formDataToSend = new FormData();
        formDataToSend.append('oportunidade_id', props.oportunidadeId.toString());
        formDataToSend.append('telefone', formData.value.telefone);
        formDataToSend.append('cv_file', cv_file.value);
        formDataToSend.append('rgpd', formData.value.rgpd ? '1' : '0');
        formDataToSend.append('recaptcha_token', props.recaptchaToken);

        // Adicionar campos adicionais se não estiver autenticado
        if (!isAuthenticated.value) {
          formDataToSend.append('nome', formData.value.nome);
          formDataToSend.append('email', formData.value.email);
          if (formData.value.linkedin) {
            formDataToSend.append('linkedin', formData.value.linkedin);
          }
        }
        
        await ApiService.submitCandidatura(formDataToSend);
        submissionStatus.value = 'success';
      } catch (error: any) {
        submissionStatus.value = 'error';
        errorMessage.value = error?.message || 'Erro desconhecido ao enviar candidatura';
        console.error('Erro ao enviar candidatura:', error);
      } finally {
        isSubmitting.value = false;
      }
    };
    
    // Redirecionar para login
    const redirectToLogin = () => {
      ApiService.redirectToSocialLogin('google');
    };
    
    // Logout do usuário
    const logout = async () => {
      try {
        await ApiService.logout();
        isAuthenticated.value = false;
        userData.value = {
          name: '',
          email: '',
          provider: '',
          provider_id: '',
          avatar: '',
          profile_url: ''
        };
        console.log('✅ Logout realizado com sucesso');
      } catch (error) {
        console.error('💥 Erro ao realizar logout:', error);
      }
    };
    
    onMounted(() => {
      // Sempre verificar autenticação quando o componente é montado
      checkAuth();
    });
    
    return {
      isAuthenticated,
      isLoading,
      userData,
      formData,
      isSubmitting,
      submissionStatus,
      errorMessage,
      handleFileUpload,
      submitCandidatura,
      redirectToLogin,
      checkAuth,
      logout
    };
  },
  computed: {
    getBadgeClass() {
      const classes = {
        'bg-danger': this.userData?.provider === 'google',
        'bg-dark': this.userData?.provider === 'github',
        'bg-primary': this.userData?.provider === 'linkedin'
      };
      return classes;
    },
    getProviderIcon() {
      const icons: Record<string, string> = {
        'google': 'fab fa-google',
        'github': 'fab fa-github',
        'linkedin': 'fab fa-linkedin'
      };
      return icons[this.userData?.provider || ''] || 'fas fa-user';
    },
    getProfileLinkText() {
      if (this.userData?.provider === 'github') {
        return 'Perfil GitHub';
      } else if (this.userData?.provider === 'linkedin') {
        return 'Perfil LinkedIn';
      }
      return 'Ver perfil';
    }
  }
});
</script>

<style scoped>
.candidatura-form-container {
  background-color: #fff;
  border-radius: 10px;
  padding: 2rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.login-required {
  text-align: center;
  padding: 1rem;
}

.user-profile-info {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.form-control-static {
  padding: 0.375rem 0;
  font-weight: 500;
}

.badge {
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
}

.badge i {
  margin-right: 0.375rem;
}
</style>
