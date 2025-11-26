import 'bootstrap';
import './bootstrap';
import 'perfect-scrollbar/dist/perfect-scrollbar.min.js';
import '@plugins/index.js';

import {createInertiaApp, Link} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {createApp, h} from 'vue';
import {ZiggyVue} from 'ziggy-js';
import vSelect from 'vue-select';
import NProgress from 'nprogress'
import toast from '@plugins/notifications.js';
import filters from '@plugins/filter.js';
import {VueTable} from '@components/global/DataTable.vue';
import datePickerPlugin from '@plugins/datePickerPlugin';

const appName = import.meta.env.VITE_APP_NAME || 'Shariff Nassir Girls Secondary School';

createInertiaApp({
   title: (title) => `${title} - ${appName}`,
   resolve: (name) =>
      resolvePageComponent(
         `./Pages/${name}.vue`,
         import.meta.glob('./Pages/**/*.vue'),
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
      color: '#4B5563',
      includeCSS: true,
      showSpinner: true,
   },
});
