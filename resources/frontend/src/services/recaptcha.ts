import { onMounted } from 'vue';

/**
 * Configura o script do reCAPTCHA
 */
export function setupRecaptcha() {
  // A configuração agora é feita diretamente no HTML
  // Este método é mantido para compatibilidade
  const recaptchaSiteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY;
  
  if (!recaptchaSiteKey) {
    console.warn('reCAPTCHA site key não configurado na variável de ambiente. Usando a chave configurada no HTML.');
  }
}

/**
 * Gera um token reCAPTCHA para uma ação específica
 * @param action Nome da ação (exemplo: 'candidatura_submit')
 * @returns Promise com o token
 */
export function getRecaptchaToken(action: string = 'submit'): Promise<string> {
  // Pegando a chave do arquivo .env ou do ambiente
  const recaptchaSiteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY || '6LcPb3QrAAAAANjd5LbOkSnpctwNTEmFoO0i0OFH';
  
  console.log('reCAPTCHA: Tentando obter token para a ação:', action);
  
  // Manter compatibilidade com ambiente de desenvolvimento
  if (!recaptchaSiteKey) {
    console.warn('reCAPTCHA site key não configurado');
    if (import.meta.env.DEV) {
      return Promise.resolve('development_mode_no_recaptcha');
    }
    return Promise.reject(new Error('reCAPTCHA não configurado'));
  }

  // Promessa para aguardar o carregamento e execução do reCAPTCHA
  return new Promise((resolve, reject) => {
    try {
      if (typeof window.grecaptcha === 'undefined') {
        console.error('reCAPTCHA não carregado no documento');
        reject(new Error('reCAPTCHA não está disponível. Verifique a conexão com internet ou recarregue a página.'));
        return;
      }
      
      window.grecaptcha.ready(() => {
        console.log('reCAPTCHA está pronto, executando para ação:', action);
        
        // Adicionar timestamp para garantir unicidade
        const actionWithTimestamp = `${action}_${Date.now()}`;
        
        window.grecaptcha
          .execute(recaptchaSiteKey, { action: actionWithTimestamp })
          .then((token: string) => {
            console.log('Token reCAPTCHA gerado com sucesso, tamanho:', token?.length);
            if (!token || token.length < 50) {
              reject(new Error('Token reCAPTCHA inválido ou muito curto'));
              return;
            }
            resolve(token);
          })
          .catch((error: any) => {
            console.error('Erro na execução do reCAPTCHA:', error);
            reject(error);
          });
      });
    } catch (error) {
      console.error('Exceção ao executar reCAPTCHA:', error);
      reject(error);
    }
  });
}

/**
 * Composable Vue para utilizar o reCAPTCHA
 * @returns Objeto com a função para obter o token
 */
export function useRecaptcha() {
  onMounted(() => {
    setupRecaptcha();
  });

  return {
    getToken: getRecaptchaToken
  };
}
