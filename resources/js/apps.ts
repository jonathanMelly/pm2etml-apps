//Handles inertia/vue root stuff
import {createInertiaApp} from "@inertiajs/inertia-vue3";
import {createApp,h,type DefineComponent} from "vue";
import {ZiggyVue} from "ziggy-js";
import {i18nVue} from "laravel-vue-i18n";

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue', { eager: true });
        let page = pages[`./pages/${name}.vue`];
        if (!page) {
            page = pages[`./pages/404.vue`];
            if (!page) {
                throw new Error(`Page ${name} not found`);
            }
        }
        return page;
    },
    setup({ el, app, props, plugin }) {
        createApp({ render: () => h(app, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(i18nVue, {
                resolve: async lang => {
                    const langs = import.meta.glob('../../lang/*.json');
                    return await langs[`../../lang/${lang}.json`]();
                }
            })
            .mount(el);
    },
});
