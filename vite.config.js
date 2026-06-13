/**
 * External dependencies
 */
import { defineConfig } from 'vite';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

export default defineConfig({
	plugins: [],
	css: {
		preprocessorOptions: {
			scss: {
				api: 'modern-compiler',
			},
		},
	},
	build: {
		outDir: 'build/assets',
		emptyOutDir: true,
		assetsInlineLimit: 100000,
		manifest: 'manifest.json',
		rollupOptions: {
			input: {
				main: resolve(__dirname, 'assets/js/main.js'),
				admin: resolve(__dirname, 'assets/js/admin.js'),
			},
			output: {
				entryFileNames: 'js/[name].js',
				chunkFileNames: 'js/[name]-[hash].js',
				assetFileNames: (assetInfo) => {
					if (assetInfo.name.endsWith('.css')) {
						return 'css/[name][extname]';
					}
					return 'assets/[name]-[hash][extname]';
				},
			},
		},
	},
	server: {
		strictPort: true,
		port: 5173,
		host: true,
		cors: true,
		origin: process.env.VITE_WP_ORIGIN,
	},
});
