export default {
    /**
     *  Application environment data from Laravel Mix (.env file)
     */
    env: {
        environment: import.meta.env.VITE_APP_ENV,
        debug: import.meta.env.VITE_APP_DEBUG,
        broadcastDriver: import.meta.env.VITE_BROADCAST_DRIVER,
        reverbAppKey: import.meta.env.VITE_REVERB_APP_KEY,
        reverbHost: import.meta.env.VITE_REVERB_HOST,
        reverbPort: import.meta.env.VITE_REVERB_PORT,
        reverbScheme: import.meta.env.VITE_REVERB_SCHEME,
        stripeKey: import.meta.env.VITE_STRIPE_KEY,
    },

    defaultColor: '#0D47A1',

    /**
     * The app url is given by Laravel in app.blade.php file
     *
     * @returns string
     */
    getAppURL(path = null) {
        const baseUrl = location.protocol +'//'+ window.location.hostname;

        if (! path) return baseUrl;

        if (!path.startsWith('/')) {
            path = `/${path}`;
        }

        return `${baseUrl}${path}`;
    },

    /**
     * The app name given by Laravel in .env (APP_NAME)
     *
     * @returns {string}
     */
    getAppName() {
        return document.head.querySelector('meta[name="app_name"]').content;
    },

    /**
     * Dynamic title of current page
     *
     * @returns {string}
     */
    getTitle() {
        return document.title;
    },

    /**
     * Company name is defined in mobius.php
     *
     * @returns {*}
     */
    getCompanyName() {
        return document.head.querySelector('meta[name="company_name"]').content;
    },

    /**
     * Detects if its in local environment
     *
     * @returns {boolean}
     */
    isLocal() {
        if (!this.env.environment) return true;

        const environment = this.env.environment.toLowerCase();

        return environment === 'local';
    }
}
