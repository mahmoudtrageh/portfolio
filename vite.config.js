import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'],
            refresh: true,
            fonts: [
                // Arabic body and headings. `subsets` is required: the plugin
                // defaults to ["latin"] only, which ships @font-face rules
                // whose unicode-range covers no Arabic at all — the font then
                // never loads and the page silently falls back to a system
                // face. Both scripts are listed so the Latin inside Arabic
                // sentences ("مهندس Backend") comes from the same family.
                bunny('Tajawal', {
                    weights: [400, 500, 700],
                    subsets: ['arabic', 'latin'],
                }),
                // Display serif for Latin headings. Arabic headings use bold
                // Tajawal instead (see resources/css/app.css) — Arabic has no
                // serif/sans distinction to mirror here.
                bunny('Bitter', {
                    weights: [500, 600],
                    subsets: ['latin'],
                }),
                // Latin body text, matched to Tajawal's tone.
                bunny('Inter', {
                    weights: [400, 500, 600, 700],
                    subsets: ['latin'],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
