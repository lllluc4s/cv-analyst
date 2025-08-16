import axios from 'axios';
import { UrlBuilder } from '@/utils/urlBuilder';

const ApiService = {
    baseURL: UrlBuilder.api(), // Usar .api() em vez de .getApiBaseUrl()
    
    init() {
        console.log('🚀 ApiService: Inicializando...');
        console.log('🔗 Base URL:', this.baseURL);
        axios.defaults.baseURL = this.baseURL;
        // Remover withCredentials - vamos usar apenas Bearer tokens
        axios.defaults.withCredentials = false;
        
        // Adicionar header ngrok-skip-browser-warning para evitar página de aviso do ngrok
        if (this.baseURL.includes('ngrok')) {
            console.log('🌐 Detectado túnel ngrok, adicionando header ngrok-skip-browser-warning');
            axios.defaults.headers.common['ngrok-skip-browser-warning'] = 'true';
        }
        
        // Adicionar token de autorização se disponível
        let token = localStorage.getItem('auth_token');
        
        // Se não encontrar no localStorage, tentar no cookie
        if (!token) {
            console.log('🍪 Verificando token em cookie...');
            token = this.getAuthCookie();
            
            // Se encontrou no cookie, salvar no localStorage
            if (token) {
                console.log('✅ Token encontrado no cookie, salvando no localStorage');
                try {
                    localStorage.setItem('auth_token', token);
                } catch (error) {
                    console.error('❌ Erro ao salvar token do cookie no localStorage:', error);
                }
            }
        }
        
        if (token) {
            console.log('✅ Token encontrado, configurando header Authorization');
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        } else {
            console.log('ℹ️ Nenhum token encontrado, usuário não autenticado');
        }
        
        console.log('✅ ApiService inicializado');
    },
    
    // Definir token de autenticação
    setAuthToken(token: string) {
        console.log('📝 ApiService: Salvando token de autenticação...');
        console.log('📝 Token: ' + token.substring(0, 10) + '...');
        
        try {
            // Salvar no localStorage
            localStorage.setItem('auth_token', token);
            console.log('✅ Token salvo no localStorage');
            
            // Salvar também em cookie como backup
            this.setAuthCookie(token);
            
            // Configurar axios headers
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            console.log('✅ Token configurado nos headers do axios');
            
            // Verificar se foi realmente salvo
            const savedToken = localStorage.getItem('auth_token');
            if (savedToken === token) {
                console.log('✅ Verificação de token: token salvo corretamente no localStorage');
            } else {
                console.error('❌ Verificação de token: o token salvo no localStorage não corresponde ao esperado!');
                console.log('Esperado:', token.substring(0, 10) + '...');
                console.log('Encontrado:', savedToken ? savedToken.substring(0, 10) + '...' : 'não encontrado');
            }
            
            // Disparar evento customizado para notificar outros componentes
            const event = new CustomEvent('auth-token-changed', { detail: { token } });
            window.dispatchEvent(event);
            console.log('✅ Evento auth-token-changed disparado');
            
        } catch (error) {
            console.error('❌ Erro ao salvar token:', error);
            // Se falhar o localStorage, pelo menos tentamos salvar no cookie
            this.setAuthCookie(token);
        }
    },
    
    // Salvar token em cookie (como backup)
    setAuthCookie(token: string) {
        try {
            const expiryDate = new Date();
            expiryDate.setDate(expiryDate.getDate() + 7); // 7 dias
            
            const cookieValue = `auth_token=${token}; expires=${expiryDate.toUTCString()}; path=/; SameSite=Lax`;
            document.cookie = cookieValue;
            
            console.log('✅ Token salvo em cookie como backup');
        } catch (error) {
            console.error('❌ Erro ao salvar token em cookie:', error);
        }
    },
    
    // Obter token do cookie
    getAuthCookie() {
        try {
            const cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                const cookie = cookies[i].trim();
                if (cookie.startsWith('auth_token=')) {
                    const token = cookie.substring('auth_token='.length);
                    console.log('✅ Token encontrado em cookie:', token.substring(0, 10) + '...');
                    return token;
                }
            }
        } catch (error) {
            console.error('❌ Erro ao obter token de cookie:', error);
        }
        return null;
    },
    
    // Remover token de autenticação
    removeAuthToken() {
        console.log('🗑️ ApiService: Removendo token de autenticação...');
        
        // Remover do localStorage
        try {
            localStorage.removeItem('auth_token');
            console.log('✅ Token removido do localStorage');
        } catch (error) {
            console.error('❌ Erro ao remover token do localStorage:', error);
        }
        
        // Remover do cookie
        try {
            document.cookie = 'auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            console.log('✅ Token removido do cookie');
        } catch (error) {
            console.error('❌ Erro ao remover token do cookie:', error);
        }
        
        // Remover do header
        delete axios.defaults.headers.common['Authorization'];
        console.log('✅ Header Authorization removido');
        
        // Disparar evento customizado
        const event = new CustomEvent('auth-token-removed');
        window.dispatchEvent(event);
        console.log('✅ Evento auth-token-removed disparado');
    },
    
    // Verificar se usuário está autenticado
    async checkAuth() {
        try {
            console.log('🔍 ApiService: Verificando autenticação...');
            
            // Tentar obter token do localStorage
            let token = localStorage.getItem('auth_token');
            
            // Se não encontrar no localStorage, tentar no cookie
            if (!token) {
                console.log('🍪 Token não encontrado no localStorage, tentando obter do cookie...');
                token = this.getAuthCookie();
                
                // Se encontrou no cookie, salvar no localStorage
                if (token) {
                    console.log('✅ Token encontrado no cookie, salvando no localStorage...');
                    localStorage.setItem('auth_token', token);
                }
            }
            
            console.log('🔑 Token encontrado:', token ? `${token.substring(0, 10)}...` : 'NÃO');
            
            if (!token) {
                console.log('❌ Sem token - usuário não autenticado');
                return false;
            }
            
            // Garantir que o header está configurado
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            console.log('✅ Header Authorization configurado para requisição');
            
            console.log('🔄 Enviando requisição para API...');
            console.log('URL:', `${this.baseURL}/api/auth/status`);
            
            const response = await axios.get(`${this.baseURL}/api/auth/status`);
            console.log('✅ Resposta da API recebida:', response.data);
            
            if (response.data.authenticated) {
                console.log('✅ Usuário autenticado com sucesso!');
                return true;
            } else {
                console.log('❌ API indica que usuário não está autenticado');
                return false;
            }
        } catch (error: any) {
            console.error('❌ Erro ao verificar autenticação:', error);
            
            // Verificar se é erro de resposta (API) ou erro de rede
            if (error.response) {
                console.error(`❌ Erro da API: ${error.response.status} - ${error.response.statusText}`);
                console.error('Detalhes:', error.response.data);
            } else if (error.request) {
                console.error('❌ Não recebeu resposta da API. Possível problema de rede.');
            } else {
                console.error('❌ Erro ao configurar requisição:', error.message);
            }
            
            return false;
        }
    },
    
    // Obter dados do usuário logado
    async getUserProfile() {
        try {
            const response = await axios.get(`${this.baseURL}/api/user/profile`);
            return response.data;
        } catch (error) {
            console.error('Erro ao obter perfil do usuário:', error);
            return { authenticated: false, user: null };
        }
    },
    
    // Submeter candidatura
    async submitCandidatura(formData: FormData) {
        try {
            // Montar headers básicos
            const headers: any = {
                'Content-Type': 'multipart/form-data',
                'Accept': 'application/json'
            };

            // Adicionar token de autorização se disponível (para usuários logados)
            const token = localStorage.getItem('auth_token');
            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }
            
            const response = await axios.post(`${this.baseURL}/api/candidaturas`, formData, {
                headers
            });
            return response.data;
        } catch (error: any) {
            console.error('Erro ao enviar candidatura:', error);
            throw error.response?.data || { message: 'Erro ao enviar candidatura' };
        }
    },
    
    // Redirecionar para o login social
    redirectToSocialLogin(provider: string) {
        window.location.href = `${this.baseURL}/auth/${provider}/redirect`;
    },

    // Logout do usuário
    async logout() {
        try {
            console.log('🚪 ApiService: Realizando logout...');
            
            // Tentar chamar endpoint de logout no backend para revogar o token
            try {
                await axios.post(`${this.baseURL}/api/auth/logout`);
                console.log('✅ Token revogado no servidor');
            } catch (serverError) {
                console.warn('⚠️ Erro ao revogar token no servidor (continuando com logout local):', serverError);
            }
            
            // Remover token de autenticação localmente (sempre executa)
            this.removeAuthToken();
            
            console.log('✅ Logout realizado com sucesso');
            return true;
        } catch (error) {
            console.error('❌ Erro ao realizar logout:', error);
            // Mesmo com erro, remover o token localmente
            this.removeAuthToken();
            return false;
        }
    }
};

// Inicializar o serviço
ApiService.init();

export default ApiService;
