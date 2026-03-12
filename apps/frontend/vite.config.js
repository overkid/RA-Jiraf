import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const mediaDir = path.resolve(__dirname, '..', '..', 'media');

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: [
      {
        find: /^\/media\//,
        replacement: `${mediaDir.replaceAll('\\', '/')}/`
      }
    ]
  },
  server: {
    fs: {
      allow: [mediaDir]
    }
  }
});
