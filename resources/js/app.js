import 'bootstrap';
import './bootstrap';

import {createInertiaApp, Link, router} from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import NProgress from 'nprogress'
import vSelect from 'vue-select';
import { VueTable } from '@components/Datable.vue';
import toast from '@plugins/notifications.js';
import filters from '@plugins/filter.js';
import datePickerPlugin from '@plugins/datePickerPlugin';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

await createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) =>
        resolvePageComponent(
           `./Pages/${name}.vue`,
           import.meta.glob('./Pages/**/*.vue')
        ),
    setup({el, App, props, plugin}) {
        return createApp({render: () => h(App, props)})
            .use(plugin)
            .use(toast)
            .use(filters)
            .use(NProgress)
            .use(ZiggyVue)
            .use(datePickerPlugin)
            .component('Link', Link)
            .component('VueTable', VueTable)
            .component('v-select', vSelect)
            .mount(el);
    },
    progress: {
        delay: 250,
        color: '#29d',
        includeCSS: true,
        showSpinner: true,
    }
});
