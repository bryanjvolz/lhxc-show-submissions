import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    outDir: 'dist',
    rollupOptions: {
      input: {
        form: resolve(__dirname, 'src/js/form.js'),
        admin: resolve(__dirname, 'src/js/admin.js'),
        style: resolve(__dirname, 'src/scss/style.scss'),
        admin_style: resolve(__dirname, 'src/scss/admin.scss')
      },
      output: {
        entryFileNames: 'js/[name].min.js',
        chunkFileNames: 'js/[name].min.js',
        assetFileNames: 'css/[name].min.[ext]'
      }
    }
  }
});