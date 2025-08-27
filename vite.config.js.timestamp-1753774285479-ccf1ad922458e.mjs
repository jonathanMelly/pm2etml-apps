// vite.config.js
import { defineConfig } from "file:///X:/pm2ETML/pm2etml-intranet/node_modules/vite/dist/node/index.js";
import laravel from "file:///X:/pm2ETML/pm2etml-intranet/node_modules/laravel-vite-plugin/dist/index.js";
import * as path from "path";
import vue from "file:///X:/pm2ETML/pm2etml-intranet/node_modules/@vitejs/plugin-vue/dist/index.mjs";
import i18n from "file:///X:/pm2ETML/pm2etml-intranet/node_modules/laravel-vue-i18n/dist/vite.mjs";
import { tscWatch } from "file:///X:/pm2ETML/pm2etml-intranet/node_modules/vite-plugin-tsc-watch/dist/index.mjs";
import { watch } from "file:///X:/pm2ETML/pm2etml-intranet/node_modules/vite-plugin-watch/dist/vite-plugin-watch.js";
var __vite_injected_original_dirname = "X:\\pm2ETML\\pm2etml-intranet";
var vite_config_default = defineConfig({
  plugins: [
    laravel([
      //css
      "resources/css/app.css",
      //mainly tailwind
      "resources/sass/app.scss",
      //mainly fa
      // TODO HCS 'resources/css/fullEvaluation.css', (missing file !!!)
      //test
      //js
      "resources/js/app.js",
      //main laravel js
      "resources/js/helper.js",
      //custom helpers
      "resources/js/jobApplication.js",
      "resources/js/dropzone.js",
      //for draq/drop file upload
      "resources/js/dashboard-charts.js",
      "resources/js/evaluation.js",
      //inertia
      "resources/js/apps.ts"
      //
    ]),
    // react(),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    }),
    i18n(),
    watch({
      pattern: "routes/**/*.php",
      command: "php artisan ziggy:generate --types-only"
    }),
    tscWatch()
  ],
  resolve: {
    alias: {
      "~fa": path.resolve(__vite_injected_original_dirname, "node_modules/@fortawesome/fontawesome-free/scss"),
      "ziggy-js": path.resolve("vendor/tightenco/ziggy")
      // avoid having ziggy in vendor+node_modules
    }
  },
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          "echarts-core": ["echarts/core"],
          "echarts-charts": ["echarts/charts"]
        }
      }
    }
  }
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJYOlxcXFxwbTJFVE1MXFxcXHBtMmV0bWwtaW50cmFuZXRcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZmlsZW5hbWUgPSBcIlg6XFxcXHBtMkVUTUxcXFxccG0yZXRtbC1pbnRyYW5ldFxcXFx2aXRlLmNvbmZpZy5qc1wiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9pbXBvcnRfbWV0YV91cmwgPSBcImZpbGU6Ly8vWDovcG0yRVRNTC9wbTJldG1sLWludHJhbmV0L3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSAndml0ZSc7XHJcbmltcG9ydCBsYXJhdmVsIGZyb20gJ2xhcmF2ZWwtdml0ZS1wbHVnaW4nO1xyXG5pbXBvcnQgKiBhcyBwYXRoIGZyb20gXCJwYXRoXCI7XHJcbi8vIGltcG9ydCByZWFjdCBmcm9tICdAdml0ZWpzL3BsdWdpbi1yZWFjdCc7XHJcbmltcG9ydCB2dWUgZnJvbSAnQHZpdGVqcy9wbHVnaW4tdnVlJztcclxuaW1wb3J0IGkxOG4gZnJvbSAnbGFyYXZlbC12dWUtaTE4bi92aXRlJztcclxuaW1wb3J0IHt0c2NXYXRjaH0gZnJvbSBcInZpdGUtcGx1Z2luLXRzYy13YXRjaFwiO1xyXG5pbXBvcnQge3dhdGNofSBmcm9tIFwidml0ZS1wbHVnaW4td2F0Y2hcIjtcclxuXHJcbmV4cG9ydCBkZWZhdWx0IGRlZmluZUNvbmZpZyh7XHJcbiAgICBwbHVnaW5zOiBbXHJcbiAgICAgICAgbGFyYXZlbChbXHJcbiAgICAgICAgICAgIC8vY3NzXHJcbiAgICAgICAgICAgICdyZXNvdXJjZXMvY3NzL2FwcC5jc3MnLCAvL21haW5seSB0YWlsd2luZFxyXG4gICAgICAgICAgICAncmVzb3VyY2VzL3Nhc3MvYXBwLnNjc3MnLCAvL21haW5seSBmYVxyXG4gICAgICAgICAgICAvLyBUT0RPIEhDUyAncmVzb3VyY2VzL2Nzcy9mdWxsRXZhbHVhdGlvbi5jc3MnLCAobWlzc2luZyBmaWxlICEhISlcclxuICAgICAgICAgICAgLy90ZXN0XHJcblxyXG4gICAgICAgICAgICAvL2pzXHJcbiAgICAgICAgICAgICdyZXNvdXJjZXMvanMvYXBwLmpzJywgLy9tYWluIGxhcmF2ZWwganNcclxuICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9oZWxwZXIuanMnLCAvL2N1c3RvbSBoZWxwZXJzXHJcblxyXG5cclxuICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9qb2JBcHBsaWNhdGlvbi5qcycsXHJcblxyXG4gICAgICAgICAgICAncmVzb3VyY2VzL2pzL2Ryb3B6b25lLmpzJywgLy9mb3IgZHJhcS9kcm9wIGZpbGUgdXBsb2FkXHJcbiAgICAgICAgICAgICdyZXNvdXJjZXMvanMvZGFzaGJvYXJkLWNoYXJ0cy5qcycsXHJcbiAgICAgICAgICAgICdyZXNvdXJjZXMvanMvZXZhbHVhdGlvbi5qcycsXHJcblxyXG4gICAgICAgICAgICAvL2luZXJ0aWFcclxuICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9hcHBzLnRzJywvL1xyXG5cclxuICAgICAgICBdKSxcclxuXHJcbiAgICAgICAgLy8gcmVhY3QoKSxcclxuICAgICAgICB2dWUoe1xyXG4gICAgICAgICAgICB0ZW1wbGF0ZToge1xyXG4gICAgICAgICAgICAgICAgdHJhbnNmb3JtQXNzZXRVcmxzOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgYmFzZTogbnVsbCxcclxuICAgICAgICAgICAgICAgICAgICBpbmNsdWRlQWJzb2x1dGU6IGZhbHNlLFxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgfSxcclxuICAgICAgICB9KSxcclxuICAgICAgICBpMThuKCksXHJcbiAgICAgICAgd2F0Y2goe1xyXG4gICAgICAgICAgICBwYXR0ZXJuOiBcInJvdXRlcy8qKi8qLnBocFwiLFxyXG4gICAgICAgICAgICBjb21tYW5kOiBcInBocCBhcnRpc2FuIHppZ2d5OmdlbmVyYXRlIC0tdHlwZXMtb25seVwiLFxyXG4gICAgICAgIH0pLFxyXG4gICAgICAgIHRzY1dhdGNoKClcclxuICAgIF0sXHJcbiAgICByZXNvbHZlOiB7XHJcbiAgICAgICAgYWxpYXM6IHtcclxuICAgICAgICAgICAgJ35mYSc6IHBhdGgucmVzb2x2ZShfX2Rpcm5hbWUsICdub2RlX21vZHVsZXMvQGZvcnRhd2Vzb21lL2ZvbnRhd2Vzb21lLWZyZWUvc2NzcycpLFxyXG4gICAgICAgICAgICAnemlnZ3ktanMnOiBwYXRoLnJlc29sdmUoJ3ZlbmRvci90aWdodGVuY28vemlnZ3knKSwvLyBhdm9pZCBoYXZpbmcgemlnZ3kgaW4gdmVuZG9yK25vZGVfbW9kdWxlc1xyXG4gICAgICAgIH1cclxuICAgIH0sXHJcbiAgICBidWlsZDoge1xyXG4gICAgICAgIHJvbGx1cE9wdGlvbnM6IHtcclxuICAgICAgICAgICAgb3V0cHV0OiB7XHJcbiAgICAgICAgICAgICAgICBtYW51YWxDaHVua3M6IHtcclxuICAgICAgICAgICAgICAgICAgICBcImVjaGFydHMtY29yZVwiOiBbJ2VjaGFydHMvY29yZSddLFxyXG4gICAgICAgICAgICAgICAgICAgIFwiZWNoYXJ0cy1jaGFydHNcIjogWydlY2hhcnRzL2NoYXJ0cyddLFxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSxcclxuICAgIH0sXHJcbn0pO1xyXG4iXSwKICAibWFwcGluZ3MiOiAiO0FBQXlRLFNBQVMsb0JBQW9CO0FBQ3RTLE9BQU8sYUFBYTtBQUNwQixZQUFZLFVBQVU7QUFFdEIsT0FBTyxTQUFTO0FBQ2hCLE9BQU8sVUFBVTtBQUNqQixTQUFRLGdCQUFlO0FBQ3ZCLFNBQVEsYUFBWTtBQVBwQixJQUFNLG1DQUFtQztBQVN6QyxJQUFPLHNCQUFRLGFBQWE7QUFBQSxFQUN4QixTQUFTO0FBQUEsSUFDTCxRQUFRO0FBQUE7QUFBQSxNQUVKO0FBQUE7QUFBQSxNQUNBO0FBQUE7QUFBQTtBQUFBO0FBQUE7QUFBQSxNQUtBO0FBQUE7QUFBQSxNQUNBO0FBQUE7QUFBQSxNQUdBO0FBQUEsTUFFQTtBQUFBO0FBQUEsTUFDQTtBQUFBLE1BQ0E7QUFBQTtBQUFBLE1BR0E7QUFBQTtBQUFBLElBRUosQ0FBQztBQUFBO0FBQUEsSUFHRCxJQUFJO0FBQUEsTUFDQSxVQUFVO0FBQUEsUUFDTixvQkFBb0I7QUFBQSxVQUNoQixNQUFNO0FBQUEsVUFDTixpQkFBaUI7QUFBQSxRQUNyQjtBQUFBLE1BQ0o7QUFBQSxJQUNKLENBQUM7QUFBQSxJQUNELEtBQUs7QUFBQSxJQUNMLE1BQU07QUFBQSxNQUNGLFNBQVM7QUFBQSxNQUNULFNBQVM7QUFBQSxJQUNiLENBQUM7QUFBQSxJQUNELFNBQVM7QUFBQSxFQUNiO0FBQUEsRUFDQSxTQUFTO0FBQUEsSUFDTCxPQUFPO0FBQUEsTUFDSCxPQUFZLGFBQVEsa0NBQVcsaURBQWlEO0FBQUEsTUFDaEYsWUFBaUIsYUFBUSx3QkFBd0I7QUFBQTtBQUFBLElBQ3JEO0FBQUEsRUFDSjtBQUFBLEVBQ0EsT0FBTztBQUFBLElBQ0gsZUFBZTtBQUFBLE1BQ1gsUUFBUTtBQUFBLFFBQ0osY0FBYztBQUFBLFVBQ1YsZ0JBQWdCLENBQUMsY0FBYztBQUFBLFVBQy9CLGtCQUFrQixDQUFDLGdCQUFnQjtBQUFBLFFBQ3ZDO0FBQUEsTUFDSjtBQUFBLElBQ0o7QUFBQSxFQUNKO0FBQ0osQ0FBQzsiLAogICJuYW1lcyI6IFtdCn0K
