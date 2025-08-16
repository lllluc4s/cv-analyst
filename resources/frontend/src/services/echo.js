import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Configurar Laravel Echo com Reverb
window.Pusher = Pusher

const echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
  wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
  authorizer: (channel, options) => {
    return {
      authorize: (socketId, callback) => {
        // Obter token de autenticação
        const token = localStorage.getItem('candidato_token') || localStorage.getItem('company_token')
        
        if (!token) {
          callback(true, { message: 'Unauthorized' })
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
          callback(false, data)
        })
        .catch(error => {
          callback(true, error)
        })
      }
    }
  },
})

export default echo
