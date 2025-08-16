import './assets/main.css'
import 'leaflet/dist/leaflet.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { setupRecaptcha } from './services/recaptcha'
import ApiService from './services/ApiService'
import axios from 'axios'

// Configurar base URL do Axios para funcionar com Laravel
const API_BASE_URL = window.location.origin + '/api'
axios.defaults.baseURL = API_BASE_URL

// Função para processar token da URL (chamada antes de tudo)
const processAuthToken = () => {
  console.log('🔍 Verificando token na URL...');
  const urlParams = new URLSearchParams(window.location.search);
  
  // Verificar o novo parâmetro _auth_token primeiro, depois o antigo token
  const token = urlParams.get('_auth_token') || urlParams.get('token');
  
  if (token) {
    console.log('✅ Token encontrado na URL:', token.substring(0, 10) + '...');
    try {
      // Salvar token no localStorage
      localStorage.setItem('auth_token', token);
      console.log('✅ Token salvo no localStorage');
      
      // Configurar token para todas as requisições axios
      ApiService.setAuthToken(token);
      console.log('✅ Token configurado para ApiService');
      
      // Limpar URL
      const url = new URL(window.location.href);
      url.searchParams.delete('_auth_token');
      url.searchParams.delete('token');
      url.searchParams.delete('auth');
      window.history.replaceState({}, document.title, url.toString());
      console.log('✅ URL limpa, token removido dos parâmetros');
    } catch (error) {
      console.error('❌ Erro ao processar token:', error);
    }
  } else {
    console.log('ℹ️ Nenhum token encontrado na URL');
    
    // Verificar se há token no localStorage e configurá-lo
    const storedToken = localStorage.getItem('auth_token');
    if (storedToken) {
      console.log('ℹ️ Token encontrado no localStorage, configurando...');
      ApiService.setAuthToken(storedToken);
    }
  }
};

// Processar token antes de inicializar a aplicação
processAuthToken();

// Adicionar listener global para eventos de autenticação
window.addEventListener('auth-token-changed', (event: any) => {
  console.log('🔔 Evento auth-token-changed capturado no escopo global');
  const token = event.detail?.token;
  if (token) {
    console.log('✅ Token recebido via evento, configurando...');
    // Configurar cabeçalhos do axios
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  }
});

// Inicializa o reCAPTCHA
setupRecaptcha()

// Inicializar ApiService (para garantir que os headers estão configurados)
ApiService.init();

// Configurar token CSRF para Laravel
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

const app = createApp(App)

app.use(router)
app.mount('#app')
