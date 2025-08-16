import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
  base: '/', // base raiz, pois o build será servido de app/public
  plugins: [vue()],
  root: 'resources/frontend',
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/frontend/src', import.meta.url)),
    },
  },
  server: {
    port: 5174,
    hmr: {
      overlay: false,
    },
    proxy: {
      '/api': {
        target: 'http://localhost:8001',
        changeOrigin: true,
      },
      '/images': {
        target: 'http://localhost:8001',
        changeOrigin: true,
      },
      '/storage': {
        target: 'http://localhost:8001',
        changeOrigin: true,
      },
    },
  },
  build: {
    outDir: '../../public', // Agora o build será feito em app/public
    emptyOutDir: true,
  },
});
