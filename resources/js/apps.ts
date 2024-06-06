//Handles inertia/vue root stuff
import {createInertiaApp} from "@inertiajs/inertia-vue3";
import {createApp,h,type DefineComponent} from "vue";
import {ZiggyVue} from "ziggy-js";

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
            .mount(el);
    },
});
