import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import { fileURLToPath } from 'node:url'

export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  server: {
    port: 5173,
    strictPort: true,
    proxy: {
      '/api': {
        target: 'http://localhost:8081',
        changeOrigin: true,
        secure: false,
      },
      '/vitrine': {
        target: 'http://localhost:5174',
        changeOrigin: true,
      },
    },
  },
  
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8081', // L'adresse de votre serveur CodeIgniter
        changeOrigin: true,
        // On réécrit l'URL : /api/register devient /api/register (on garde le /api car vos routes CI4 commencent par 'api/')
        rewrite: (path) => path.replace(/^\/api/, '/api') 
      }
    }
  }
})
