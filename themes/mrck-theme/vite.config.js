import { defineConfig } from 'vite';

// Assets are served from the theme's /dist folder; PHP resolves the final URL
// from the manifest, so `base` stays empty.
export default defineConfig({
  base: '',
  build: {
    manifest: true,
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      input: 'src/js/main.js',
    },
  },
  server: {
    host: 'localhost',
    port: 5173,
    strictPort: true,
    cors: true,
    origin: 'http://localhost:5173',
  },
});
