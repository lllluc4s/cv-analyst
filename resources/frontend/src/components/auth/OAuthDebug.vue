<template>
  <div v-if="debug" class="oauth-debug-panel">
    <div class="oauth-debug-header">
      <h3>Debug OAuth</h3>
      <button @click="debug = false" class="close-btn">&times;</button>
    </div>
    <div class="oauth-debug-content">
      <div class="debug-status">
        <strong>Status:</strong> 
        <span :class="isAuthenticated ? 'status-success' : 'status-error'">
          {{ isAuthenticated ? 'Autenticado' : 'Não Autenticado' }}
        </span>
      </div>
      
      <div class="debug-token" v-if="token">
        <strong>Token:</strong> {{ tokenPreview }}
      </div>
      
      <div class="debug-user" v-if="user">
        <strong>Usuário:</strong> {{ user.name }} ({{ user.email }})
      </div>
      
      <div class="debug-actions">
        <button @click="checkAuthStatus" class="debug-btn">Verificar Autenticação</button>
        <button @click="clearToken" class="debug-btn danger">Limpar Token</button>
      </div>
      
      <div class="debug-log">
        <strong>Log:</strong>
        <pre>{{ logMessages.join('\n') }}</pre>
      </div>
    </div>
  </div>
  
  <div v-else class="oauth-debug-trigger">
    <button @click="debug = true" class="debug-trigger-btn">Debug OAuth</button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import ApiService from '../../services/ApiService';

const debug = ref(false);
const isAuthenticated = ref(false);
const token = ref('');
const user = ref<any>(null);
const logMessages = ref<string[]>([]);

// Extrair e usar token da URL
const extractTokenFromUrl = () => {
  log('Verificando token na URL...');
  const urlParams = new URLSearchParams(window.location.search);
  const urlToken = urlParams.get('token');
  
  if (urlToken) {
    log(`Token encontrado na URL: ${urlToken.substring(0, 10)}...`);
    saveToken(urlToken);
    
    // Limpar URL
    const url = new URL(window.location.href);
    url.searchParams.delete('token');
    window.history.replaceState({}, document.title, url.toString());
    log('URL limpa, token removido da query string');
  } else {
    log('Nenhum token encontrado na URL');
  }
};

// Salvar token
const saveToken = (newToken: string) => {
  log(`Salvando token: ${newToken.substring(0, 10)}...`);
  localStorage.setItem('auth_token', newToken);
  token.value = newToken;
  
  // Configurar para axios
  try {
    log('Configurando header de autorização para API');
    ApiService.setAuthToken(newToken);
  } catch (error) {
    log(`Erro ao configurar token para API: ${error}`);
  }
};

// Verificar status de autenticação
const checkAuthStatus = async () => {
  log('Verificando status de autenticação...');
  
  // Verificar token salvo
  const storedToken = localStorage.getItem('auth_token');
  if (storedToken) {
    token.value = storedToken;
    log(`Token encontrado no localStorage: ${storedToken.substring(0, 10)}...`);
  } else {
    log('Nenhum token encontrado no localStorage');
    isAuthenticated.value = false;
    return;
  }
  
  try {
    // Verificar autenticação na API
    log('Verificando token na API...');
    const authStatus = await ApiService.checkAuth();
    isAuthenticated.value = authStatus;
    
    if (authStatus) {
      log('Token válido! Usuário está autenticado.');
      
      // Obter dados do usuário
      try {
        const profileData = await ApiService.getUserProfile();
        if (profileData?.user) {
          user.value = profileData.user;
          log(`Dados do usuário: ${user.value.name} (${user.value.email})`);
        }
      } catch (profileError) {
        log(`Erro ao obter perfil: ${profileError}`);
      }
    } else {
      log('Token inválido ou expirado. Usuário não está autenticado.');
    }
  } catch (error) {
    log(`Erro ao verificar autenticação: ${error}`);
    isAuthenticated.value = false;
  }
};

// Limpar token
const clearToken = () => {
  log('Removendo token...');
  localStorage.removeItem('auth_token');
  token.value = '';
  isAuthenticated.value = false;
  user.value = null;
  log('Token removido, usuário deslogado');
};

// Função para log
const log = (message: string) => {
  const timestamp = new Date().toLocaleTimeString();
  logMessages.value.push(`[${timestamp}] ${message}`);
  console.log(`[OAuthDebug] ${message}`);
  
  // Limitar tamanho do log
  if (logMessages.value.length > 30) {
    logMessages.value = logMessages.value.slice(-30);
  }
};

// Preview do token
const tokenPreview = computed(() => {
  if (!token.value) return 'Nenhum';
  return `${token.value.substring(0, 15)}...`;
});

// Verificar token ao montar o componente
onMounted(() => {
  log('Componente de debug OAuth montado');
  extractTokenFromUrl();
  checkAuthStatus();
  
  // Adicionar listener para evento personalizado
  window.addEventListener('oauth-token-received', (event: any) => {
    const receivedToken = event.detail?.token;
    if (receivedToken) {
      log(`Token recebido via evento: ${receivedToken.substring(0, 10)}...`);
      saveToken(receivedToken);
      checkAuthStatus();
    }
  });
});

onUnmounted(() => {
  window.removeEventListener('oauth-token-received', () => {});
});
</script>

<style scoped>
.oauth-debug-panel {
  position: fixed;
  bottom: 10px;
  right: 10px;
  width: 350px;
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  z-index: 9999;
  font-family: monospace;
  font-size: 12px;
}

.oauth-debug-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: #e9ecef;
  border-bottom: 1px solid #dee2e6;
  border-radius: 6px 6px 0 0;
}

.oauth-debug-header h3 {
  margin: 0;
  font-size: 14px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #6c757d;
}

.oauth-debug-content {
  padding: 12px;
}

.debug-status, .debug-token, .debug-user {
  margin-bottom: 8px;
}

.status-success {
  color: #28a745;
  font-weight: bold;
}

.status-error {
  color: #dc3545;
  font-weight: bold;
}

.debug-actions {
  margin: 10px 0;
  display: flex;
  gap: 8px;
}

.debug-btn {
  padding: 4px 8px;
  font-size: 12px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.debug-btn.danger {
  background: #dc3545;
}

.debug-log {
  margin-top: 10px;
}

.debug-log pre {
  background: #f1f1f1;
  padding: 8px;
  border-radius: 4px;
  max-height: 150px;
  overflow-y: auto;
  font-size: 11px;
  white-space: pre-wrap;
}

.oauth-debug-trigger {
  position: fixed;
  bottom: 10px;
  right: 10px;
  z-index: 9999;
}

.debug-trigger-btn {
  padding: 5px 10px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  opacity: 0.7;
}

.debug-trigger-btn:hover {
  opacity: 1;
}
</style>
