import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import Translator from '@/objects/Translator';
import useHttp from '@/composables/useHttp';
import { useRouter } from 'vue-router';

const groupMap = {
    system: ['admin'],
    managers: ['manager'],
    users: ['user'],
    internals: ['admin', 'manager'],
    externals: ['user']
};

export const useAuth = defineStore('auth', () => {
    const { getJson, postJson, putJson, loading } = useHttp();
    const { push } = useRouter();

    const user = ref(null);
    const socketId = ref(null);
    const isLogged = computed(() => !! user.value?.id);
    const isInternal = computed(() => hasGroup('internals'));
    const isAdmin = computed(() => hasRole('admin'));
    const flagsData = computed(() => [
        {
            locale: 'en',
            icon: 'gb',
            label: Translator.tl('locales.en')
        },
        {
            locale: 'es',
            icon: 'es',
            label: Translator.tl('locales.es')
        },
    ]);
    const currentFlag = computed(() => flagsData.value.find(flagData => flagData.locale === Translator.getLocale()));

    const tax = computed(() => 21); // TODO: calculate tax

    const setUser = (data) => {
        if (! data.id) {
            return user.value = null;
        }

        setLocale(data.locale);

        user.value = data;
    }

    const setLocale = (locale) => {
        if (! locale) return;

        Translator.setLocale(locale);

        if (user.value) {
            user.value.locale = locale;
        }
    }

    const setSocketId = (id) => {
        socketId.value = id;
    }

    const impersonate = async (userId) => {
        const { data } = await postJson(`users/${ userId }/impersonate`);
        user.value = data;
    }

    const login = async (form) => {
        const { data } = await postJson('login', form)
        user.value = data;
    }

    const logout = async () => {
        await postJson('logout');

        await push({name: 'welcome'});

        user.value = null;
    }

    const fetchMe = async () => {
        try {
            const { data } = await getJson('auth/me', { quietly: true });
            user.value = data;
        } catch (err) {
            user.value = null;
        }
    }

    // The visitor's identity, requested only once per page load so route guards can wait for it
    let identity = null;
    const loadIdentity = () => identity ??= fetchMe();

    const changeLocale = async (locale) => {
        if (! locale ||user.value?.locale === locale) return;

        await putJson('locales', { locale });

        setLocale(locale);
    }

    /**
     *
     * @param {string} roleName
     * @param {boolean} strict
     * @param {*} otherUser
     * @returns {boolean}
     */
    function hasRole(roleName, strict = false, otherUser = null) {
        const targetUser = otherUser || user.value;

        if (! targetUser?.id) return false;

        if (! strict && targetUser.role === 'admin') return true;

        return targetUser.role === roleName;
    }

    /**
     *
     * @param {string} groupName
     * @param {boolean} strict
     * @param {*} otherUser
     * @returns {boolean}
     */
    const hasGroup = (groupName, strict = false, otherUser = null) => {
        const targetUser = otherUser || user.value;

        if (! targetUser?.id) return false;

        if (! strict && hasRole('admin')) return true;

        if (! groupMap[groupName]) throw `Unknown group ${ groupName }`;

        return targetUser.role === groupMap[groupName];
    }

    return {
        user,
        socketId,
        isLogged,
        isInternal,
        isAdmin,
        flagsData,
        currentFlag,
        tax,
        loading,
        setUser,
        setLocale,
        setSocketId,
        impersonate,
        login,
        logout,
        fetchMe,
        loadIdentity,
        changeLocale,
        hasRole,
        hasGroup,
    }
});
