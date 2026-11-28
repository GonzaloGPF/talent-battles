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

const PRELOAD_RELOAD_KEY = 'vite_preload_reload_at';
const PRELOAD_RELOAD_INTERVAL = 10000;

/**
 * After a deployment, a page loaded before it asks for chunks of the old build, which are gone, and reloading loads the
 * new one. Only once every few seconds: a chunk missing from the new build too would reload the page forever. Without
 * session storage a loop cannot be told apart, so the error is left to the router.
 */
window.addEventListener('vite:preloadError', (event) => {
    try {
        const lastReload = Number(sessionStorage.getItem(PRELOAD_RELOAD_KEY)) || 0;

        if (Date.now() - lastReload < PRELOAD_RELOAD_INTERVAL) return;

        sessionStorage.setItem(PRELOAD_RELOAD_KEY, String(Date.now()));
    } catch {
        return;
    }

    event.preventDefault();
    window.location.reload();
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
const app = createApp(AppRoot);

registerPlugins(app);

app.mount('#app');

export default app;
