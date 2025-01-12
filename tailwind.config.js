import defaultTheme from 'tailwindcss/defaultTheme';
import daisyui from "daisyui";
import typography from "@tailwindcss/typography"

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php', //cached views
        './resources/views/**/*.blade.php', //source files
    ],
    safelist:[
        'progress-error',
        'progress-info',
        'progress-warning',
        'progress-accent',
        'progress-neutral',
        'alert-success',
        'alert-info',
        'alert-warning',
        'alert-error',
        'bg-success',
        'bg-error',
        'bg-warning',
        'loading',
        'loading-spinner',
        'btn-disabled'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [daisyui,typography],

    daisyui: {
        styled: true,
        themes: ["light", "dark", "cupcake", "bumblebee", "emerald", "corporate", "synthwave", "retro", "cyberpunk", "valentine", "halloween", "garden", "forest", "aqua", "lofi", "pastel", "fantasy", "wireframe", "black", "luxury", "dracula", "cmyk", "autumn", "business", "acid", "lemonade", "night", "coffee", "winter"],
        base: true,
        utils: true,
        logs: true,
        rtl: false,
        prefix: "",
        darkTheme: "dark",
    },
};
