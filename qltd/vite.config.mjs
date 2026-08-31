import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import zmp from "zmp-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import { fileURLToPath, URL } from "url";

import legacy from '@vitejs/plugin-legacy';

// https://vitejs.dev/config/
export default defineConfig({
  base: './',
  plugins: [
    tailwindcss(), 
    zmp(), 
    react(),
    legacy({
      targets: ['defaults', 'not IE 11']
    })
  ],
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./src", import.meta.url)),
    },
  },
  server: {
    proxy: {
      '/api/news.php': {
        target: 'https://c3binhson.edu.vn',
        changeOrigin: true,
      }
    }
  }
});
