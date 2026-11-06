/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import '@/../sass/app.scss'
import '@/vendor/laravel-echo';
import registerPlugins from '@/plugins';
import AppRoot from '@/components/core/AppRoot.vue';
import { createApp } from 'vue';

// https://stackoverflow.com/questions/69300341/typeerror-failed-to-fetch-dynamically-imported-module-on-vue-vite-vanilla-set
window.addEventListener('vite:preloadError', () => {
    window.location.reload()
})

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
const app = createApp(AppRoot);

registerPlugins(app);

app.mount('#app');

export default app;
