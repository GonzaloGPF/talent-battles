import i18n, { options } from '@/plugins/i18n';
import router from '@/plugins/router';
import vuetify from '@/plugins/vuetify';
import socialSharing from '@/plugins/social-sharing';
import pinia from '@/plugins/pinia';

export default app => {
    app.use(router)
        .use(i18n, options)
        .use(pinia)
        .use(vuetify)
        .use(socialSharing);
}

