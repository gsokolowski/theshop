import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { createHtmlPlugin } from 'vite-plugin-html'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  // ✅ ADDED: load VITE_HMR_* from .env* so Sail nginx (https://shop-local.codecreators.co.uk) can proxy HMR
  const env = loadEnv(mode, process.cwd(), '')
  const sailHmr = Boolean(env.VITE_HMR_HOST)

  return {
    plugins: [
      vue(),
      vueDevTools(),
      createHtmlPlugin(),
    ],
    server: {
      host: true,
      port: 5173,
      strictPort: sailHmr,
      ...(sailHmr
        ? {
            hmr: {
              host: env.VITE_HMR_HOST,
              protocol: env.VITE_HMR_PROTOCOL || 'wss',
              clientPort: Number(env.VITE_HMR_CLIENT_PORT || 443),
            },
            watch: {
              usePolling: true,
            },
          }
        : {}),
    },
    test: {
      globals: true,
      environment: 'jsdom',
      include: ['src/**/*.{test,spec}.{js,ts,vue}'],
      setupFiles: ['src/test/setup.js'],
    },
  }
})
