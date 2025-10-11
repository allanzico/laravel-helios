
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { tanstackRouter } from '@tanstack/router-plugin/vite'
import tsconfigPaths from 'vite-tsconfig-paths';
import path from 'path';

export default defineConfig({
  plugins: [tanstackRouter({
    target: 'react',
    autoCodeSplitting: true,
  }), react(), tailwindcss(),tsconfigPaths()],
    resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  build: {
    outDir: '../public',
    emptyOutDir: true,
    manifest: true,
  },
  server: {
    proxy: {
      '/scout/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
      },
    },
  },
});