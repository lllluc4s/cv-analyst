import { fileURLToPath, URL } from 'node:url'

import vue from '@vitejs/plugin-vue'
import { defineConfig, loadEnv } from 'vite'

// https://vite.dev/config/
export default defineConfig(({ command, mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  return {
    plugins: [
      vue(),
      // vueDevTools(), // desabilitado para evitar erro de overlay em dev
    ],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url))
      },
    },
    server: {
      proxy: {
        '/api': {
          target: env.VITE_API_URL || 'http://localhost:8001',
          changeOrigin: true,
        }
      },
      port: 5174
    },
    build: {
      outDir: '../../public/frontend',
      emptyOutDir: true,
    }
  }
})
