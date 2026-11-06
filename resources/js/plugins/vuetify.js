import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';
import { h } from 'vue';
import { aliases, mdi } from 'vuetify/iconsets/mdi';
import { createVuetify } from 'vuetify';
import icons from '@/components/core/icons';
import colors from '@/config/colors';

export default createVuetify({
    theme: {
        defaultTheme: 'dark',
        themes: {
            dark: {
                dark: true,
                colors
            },
        },
    },
    icons: {
        defaultSet: 'mdi',
        aliases: {
            ...aliases,
            ...icons
        },
        sets: {
            mdi,
            svg: {
                component: (props) => h('i', { class: ['fib', `fi-${props.icon}`] })
            }
            // custom: {
            //     component: (props) => h(props.tag, { class: [props.icon] })
            // },
        },
    }
    // lang: {
    //     t: (key, ...params) => i18n.global.t(key, params),
    // }
});
