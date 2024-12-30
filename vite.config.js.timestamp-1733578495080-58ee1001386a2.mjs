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
      //test
      //js
      "resources/js/app.js",
      //main laravel js
      "resources/js/helper.js",
      //custom helpers
      "resources/js/dropzone.js",
      //for draq/drop file upload
      "resources/js/dashboard-charts.js",
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
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJYOlxcXFxwbTJFVE1MXFxcXHBtMmV0bWwtaW50cmFuZXRcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZmlsZW5hbWUgPSBcIlg6XFxcXHBtMkVUTUxcXFxccG0yZXRtbC1pbnRyYW5ldFxcXFx2aXRlLmNvbmZpZy5qc1wiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9pbXBvcnRfbWV0YV91cmwgPSBcImZpbGU6Ly8vWDovcG0yRVRNTC9wbTJldG1sLWludHJhbmV0L3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSAndml0ZSc7XHJcbmltcG9ydCBsYXJhdmVsIGZyb20gJ2xhcmF2ZWwtdml0ZS1wbHVnaW4nO1xyXG5pbXBvcnQgKiBhcyBwYXRoIGZyb20gXCJwYXRoXCI7XHJcbi8vIGltcG9ydCByZWFjdCBmcm9tICdAdml0ZWpzL3BsdWdpbi1yZWFjdCc7XHJcbmltcG9ydCB2dWUgZnJvbSAnQHZpdGVqcy9wbHVnaW4tdnVlJztcclxuaW1wb3J0IGkxOG4gZnJvbSAnbGFyYXZlbC12dWUtaTE4bi92aXRlJztcclxuaW1wb3J0IHt0c2NXYXRjaH0gZnJvbSBcInZpdGUtcGx1Z2luLXRzYy13YXRjaFwiO1xyXG5pbXBvcnQge3dhdGNofSBmcm9tIFwidml0ZS1wbHVnaW4td2F0Y2hcIjtcclxuXHJcbmV4cG9ydCBkZWZhdWx0IGRlZmluZUNvbmZpZyh7XHJcbiAgICBwbHVnaW5zOiBbXHJcbiAgICAgICAgbGFyYXZlbChbXHJcbiAgICAgICAgICAgIC8vY3NzXHJcbiAgICAgICAgICAgICdyZXNvdXJjZXMvY3NzL2FwcC5jc3MnLCAvL21haW5seSB0YWlsd2luZFxyXG4gICAgICAgICAgICAncmVzb3VyY2VzL3Nhc3MvYXBwLnNjc3MnLCAvL21haW5seSBmYVxyXG5cclxuICAgICAgICAgICAgLy90ZXN0XHJcblxyXG4gICAgICAgICAgICAvL2pzXHJcbiAgICAgICAgICAgICdyZXNvdXJjZXMvanMvYXBwLmpzJywgLy9tYWluIGxhcmF2ZWwganNcclxuICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9oZWxwZXIuanMnLCAvL2N1c3RvbSBoZWxwZXJzXHJcblxyXG5cclxuICAgICAgICAgICAgJ3Jlc291cmNlcy9qcy9kcm9wem9uZS5qcycsIC8vZm9yIGRyYXEvZHJvcCBmaWxlIHVwbG9hZFxyXG4gICAgICAgICAgICAncmVzb3VyY2VzL2pzL2Rhc2hib2FyZC1jaGFydHMuanMnLFxyXG5cclxuICAgICAgICAgICAgLy9pbmVydGlhXHJcbiAgICAgICAgICAgICdyZXNvdXJjZXMvanMvYXBwcy50cycsLy9cclxuXHJcbiAgICAgICAgXSksXHJcblxyXG4gICAgICAgIC8vIHJlYWN0KCksXHJcbiAgICAgICAgdnVlKHtcclxuICAgICAgICAgICAgdGVtcGxhdGU6IHtcclxuICAgICAgICAgICAgICAgIHRyYW5zZm9ybUFzc2V0VXJsczoge1xyXG4gICAgICAgICAgICAgICAgICAgIGJhc2U6IG51bGwsXHJcbiAgICAgICAgICAgICAgICAgICAgaW5jbHVkZUFic29sdXRlOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgIH0sXHJcbiAgICAgICAgfSksXHJcbiAgICAgICAgaTE4bigpLFxyXG4gICAgICAgIHdhdGNoKHtcclxuICAgICAgICAgICAgcGF0dGVybjogXCJyb3V0ZXMvKiovKi5waHBcIixcclxuICAgICAgICAgICAgY29tbWFuZDogXCJwaHAgYXJ0aXNhbiB6aWdneTpnZW5lcmF0ZSAtLXR5cGVzLW9ubHlcIixcclxuICAgICAgICB9KSxcclxuICAgICAgICB0c2NXYXRjaCgpXHJcbiAgICBdLFxyXG4gICAgcmVzb2x2ZToge1xyXG4gICAgICAgIGFsaWFzOiB7XHJcbiAgICAgICAgICAgICd+ZmEnOiBwYXRoLnJlc29sdmUoX19kaXJuYW1lLCAnbm9kZV9tb2R1bGVzL0Bmb3J0YXdlc29tZS9mb250YXdlc29tZS1mcmVlL3Njc3MnKSxcclxuICAgICAgICAgICAgJ3ppZ2d5LWpzJzogcGF0aC5yZXNvbHZlKCd2ZW5kb3IvdGlnaHRlbmNvL3ppZ2d5JyksLy8gYXZvaWQgaGF2aW5nIHppZ2d5IGluIHZlbmRvcitub2RlX21vZHVsZXNcclxuICAgICAgICB9XHJcbiAgICB9LFxyXG4gICAgYnVpbGQ6IHtcclxuICAgICAgICByb2xsdXBPcHRpb25zOiB7XHJcbiAgICAgICAgICAgIG91dHB1dDoge1xyXG4gICAgICAgICAgICAgICAgbWFudWFsQ2h1bmtzOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgXCJlY2hhcnRzLWNvcmVcIjogWydlY2hhcnRzL2NvcmUnXSxcclxuICAgICAgICAgICAgICAgICAgICBcImVjaGFydHMtY2hhcnRzXCI6IFsnZWNoYXJ0cy9jaGFydHMnXSxcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0sXHJcbiAgICB9LFxyXG59KTtcclxuIl0sCiAgIm1hcHBpbmdzIjogIjtBQUF5USxTQUFTLG9CQUFvQjtBQUN0UyxPQUFPLGFBQWE7QUFDcEIsWUFBWSxVQUFVO0FBRXRCLE9BQU8sU0FBUztBQUNoQixPQUFPLFVBQVU7QUFDakIsU0FBUSxnQkFBZTtBQUN2QixTQUFRLGFBQVk7QUFQcEIsSUFBTSxtQ0FBbUM7QUFTekMsSUFBTyxzQkFBUSxhQUFhO0FBQUEsRUFDeEIsU0FBUztBQUFBLElBQ0wsUUFBUTtBQUFBO0FBQUEsTUFFSjtBQUFBO0FBQUEsTUFDQTtBQUFBO0FBQUE7QUFBQTtBQUFBLE1BS0E7QUFBQTtBQUFBLE1BQ0E7QUFBQTtBQUFBLE1BR0E7QUFBQTtBQUFBLE1BQ0E7QUFBQTtBQUFBLE1BR0E7QUFBQTtBQUFBLElBRUosQ0FBQztBQUFBO0FBQUEsSUFHRCxJQUFJO0FBQUEsTUFDQSxVQUFVO0FBQUEsUUFDTixvQkFBb0I7QUFBQSxVQUNoQixNQUFNO0FBQUEsVUFDTixpQkFBaUI7QUFBQSxRQUNyQjtBQUFBLE1BQ0o7QUFBQSxJQUNKLENBQUM7QUFBQSxJQUNELEtBQUs7QUFBQSxJQUNMLE1BQU07QUFBQSxNQUNGLFNBQVM7QUFBQSxNQUNULFNBQVM7QUFBQSxJQUNiLENBQUM7QUFBQSxJQUNELFNBQVM7QUFBQSxFQUNiO0FBQUEsRUFDQSxTQUFTO0FBQUEsSUFDTCxPQUFPO0FBQUEsTUFDSCxPQUFZLGFBQVEsa0NBQVcsaURBQWlEO0FBQUEsTUFDaEYsWUFBaUIsYUFBUSx3QkFBd0I7QUFBQTtBQUFBLElBQ3JEO0FBQUEsRUFDSjtBQUFBLEVBQ0EsT0FBTztBQUFBLElBQ0gsZUFBZTtBQUFBLE1BQ1gsUUFBUTtBQUFBLFFBQ0osY0FBYztBQUFBLFVBQ1YsZ0JBQWdCLENBQUMsY0FBYztBQUFBLFVBQy9CLGtCQUFrQixDQUFDLGdCQUFnQjtBQUFBLFFBQ3ZDO0FBQUEsTUFDSjtBQUFBLElBQ0o7QUFBQSxFQUNKO0FBQ0osQ0FBQzsiLAogICJuYW1lcyI6IFtdCn0K
