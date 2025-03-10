import 'bootstrap';
import '@popperjs/core';
import './bootstrap';
import 'perfect-scrollbar/dist/perfect-scrollbar.min.js';
import 'boxicons/dist/boxicons.js';

import { createApp, h } from 'vue';
import BootstrapVue3 from 'bootstrap-vue-3';
import NProgress from 'nprogress'
import {createInertiaApp, Link, router} from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import DefaultLayout from '@components/layouts/DefaultLayout.vue';
import { VueTable } from '@components/pages/Datable.vue';
// import { QuillEditor } from '@components/global/QuillEditor.vue';
import toast from '@plugins/notifications.js';
import vSelect from 'vue-select';
import filters from '@plugins/filter.js';
import datePickerPlugin from '@plugins/datePickerPlugin';
import { ZiggyVue } from 'ziggy-js';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${appName}`,
    // resolve: (name) => resolvePageComponent(`./components/pages/${name}.vue`, import.meta.glob('./components/pages/**/*.vue')),
    resolve: async (name) => {
        const page = await resolvePageComponent(`./components/pages/${name}.vue`, import.meta.glob('./components/pages/**/*.vue'));

        // Automatically set the default layout if not specified in the page component
        page.default.layout = page.default.layout || DefaultLayout;

        return page;
    },
    setup({el, App, props, plugin}) {
        createApp({render: () => h(App, props)})
            .use(plugin)
            .use(BootstrapVue3) // Register BootstrapVue3
            .use(toast)
            .use(filters)
            .use(NProgress)
            .use(ZiggyVue)
            .use(datePickerPlugin)
            .component('Link', Link)
            .component('VueTable', VueTable)
            .component('v-select', vSelect)
            // .component('QuillEditor', QuillEditor)
            // .component('ckeditor', CKEditor.component)
            .mount(el);
    },
    progress: {
        delay: 250,
        color: '#29d',
        includeCSS: true,
        showSpinner: true,
    }
});
