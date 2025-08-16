import axios from 'axios';
import { UrlBuilder } from '@/utils/urlBuilder';

const apiUrl = UrlBuilder.getApiBaseUrl();

// Configuração global do Axios
axios.defaults.baseURL = apiUrl;
axios.defaults.withCredentials = true; // Importante para autenticação baseada em cookies

// Adicionar header ngrok-skip-browser-warning se usando ngrok
if (apiUrl.includes('ngrok')) {
    axios.defaults.headers.common['ngrok-skip-browser-warning'] = 'true';
}

const AuthService = {
    // Método para obter token CSRF
    async getCsrfToken() {
        await axios.get(`${apiUrl}/sanctum/csrf-cookie`);
    },

    // Verificar autenticação
    async checkAuth() {
        try {
            const response = await axios.get('/auth/status');
            return response.data.authenticated;
        } catch (error) {
            console.error("Erro ao verificar autenticação:", error);
            return false;
        }
    },

    // Obter dados do usuário
    async getUser() {
        try {
            const response = await axios.get('/user/profile');
            return response.data;
        } catch (error) {
            console.error("Erro ao obter dados do usuário:", error);
            return { authenticated: false, user: null };
        }
    },
    
    // Redirecionar para login social
    redirectToSocialLogin(provider: string) {
        window.location.href = `${apiUrl}/auth/${provider}/redirect`;
    },
    
    // Método para submeter candidatura
    async submitCandidatura(formData: FormData) {
        try {
            // Garantir que temos um token CSRF antes
            await this.getCsrfToken();
            
            const response = await axios.post('/candidaturas', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            
            return response.data;
        } catch (error: any) {
            console.error("Erro ao enviar candidatura:", error.response || error);
            throw error;
        }
    }
};

export default AuthService;
