import { defineConfig, searchForWorkspaceRoot } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const mediaDir = path.resolve(__dirname, '..', '..', 'media');
const normalizedMediaDir = mediaDir.replaceAll('\\', '/');

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: [
      {
        find: /^\/media\//,
        replacement: `/@fs/${normalizedMediaDir}/`
      }
    ]
  },
  server: {
    fs: {
      allow: [searchForWorkspaceRoot(process.cwd()), mediaDir]
    }
  }
});
