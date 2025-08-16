import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

declare global {
  interface Window {
    Pusher: typeof Pusher
  }
}

// Configurar Laravel Echo com Reverb
window.Pusher = Pusher

const echo: any = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
  wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
  authorizer: (channel: any, options: any) => {
    return {
      authorize: (socketId: string, callback: (error: any, data?: any) => void) => {
        // Obter token de autenticação
        const token = localStorage.getItem('candidato_token') || localStorage.getItem('company_token')
        
        if (!token) {
          callback(new Error('Unauthorized'))
          return
        }

        // Fazer requisição para autorizar o canal
        fetch('/api/broadcasting/auth', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            socket_id: socketId,
            channel_name: channel.name,
          }),
        })
        .then(response => response.json())
        .then(data => {
          callback(null, data)
        })
        .catch(error => {
          callback(error)
        })
      }
    }
  },
})

export default echo
