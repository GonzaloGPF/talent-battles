import global from '@/config/global';
import { serialize } from 'object-to-formdata';
import _ from 'lodash';
import { useAuth } from '@/stores/auth';
import qs from 'qs';
import Translator from '@/objects/Translator';
import Time from '@/objects/Time';

const Utils = {
    isProduction: () => global.env.environment === 'production',

    /**
     * It generates a FormData object from a given object. Useful when sending files in a form.
     *
     * @param {*} obj
     * @returns {FormData}
     */
    toFormData: (obj) => {
        return serialize(obj, {
            indices: true,
            booleansAsIntegers: true,
        });
    },

    /**
     * Transforms given values in an array of objects with id, value and label
     * @param {array} values
     * @param {function|null} translator
     * @returns {array}
     */
    options: (values, translator = null) => {
        if (! Array.isArray(values)) return [];

        return values.map((value) => ({
            id: value,
            value: value,
            label: translator
                ? translator(value)
                : Translator.translate(value),
        }))
    },

    /**
     * Deep copy of an Object. You can pass an except array to remove desired keys
     *
     * @param obj
     * @param except
     * @returns {any}
     */
    copy: (obj, except = []) => {
        let result = { ...obj };

        except.forEach(key => delete result[key]);

        return JSON.parse(JSON.stringify(result)); // This destroys File variables
    },

    /**
     * Returns the given object, removing all keys with empty values
     * @param obj
     * @returns {{}|*}
     */
    removeEmpty: (obj) => {
        if (! obj) return {};

        obj = JSON.parse(JSON.stringify(obj)); // deep clone

        Object.keys(obj).forEach(k =>
            (obj[k] && typeof obj[k] === 'object')
            && Utils.removeEmpty(obj[k])
            || Utils.isEmptyValue(obj[k]) && delete obj[k]
        );

        return obj;
    },

    /**
     * Checks if a given value is empty
     * @param value
     * @returns {boolean}
     */
    isEmptyValue: (value) => {
        if (Array.isArray(value)) return value.length === 0;

        return value === '' || value === null || value === undefined;
    },

    /**
     *
     * @param obj1
     * @param obj2
     * @returns {boolean}
     */
    areEquals: (obj1, obj2) => {
        return _.isEqual(Utils.removeEmpty(obj1), Utils.removeEmpty(obj2));
    },

    /**
     *
     * @param file
     * @param {boolean} cacheBreaking
     * @returns {string|null}
     */
    getFileURL: (file, cacheBreaking = false) => {
        if (! file) return Utils.getFullUrl('/images/logo.svg');

        let params = {};

        const { user } = useAuth();

        if (user && user.locale) {
            params.locale = user.locale;
        }

        if (cacheBreaking) {
            params.cache = Time.now().getTime();
        }

        let query = qs.stringify(params);

        let url = Utils.getFullUrl(`/api/files/${ file.id }`);

        if (query.length) {
            return `${ url }?${ query }`
        }

        return url;
    },

    /**
     * @param {string} path
     */
    getFullUrl: (path) => {
        if (!path) return '';

        let appUrl = global.getAppURL();

        if (appUrl.endsWith('/')) {
            appUrl = appUrl.slice(0, -1);
        }

        if (path.startsWith('/')) {
            path = path.slice(1, appUrl.length);
        }

        return `${ appUrl }/${ path }`;
    },

    /**
     *
     * @param path
     * @returns {Promise<unknown>|boolean}
     */
    openTab: (path) => {
        if (! path) {
            return false;
        }

        let url = null;

        if (path.startsWith('http')) {
            url = path;
        } else if (! path.startsWith('/')) {
            url = global.getAppURL() + '/' + path;
        }

        const tab = window.open(url, '_blank');

        tab.focus();

        return new Promise(resolve => {
            tab.addEventListener('beforeunload', () => resolve());
        });
    },
}

export default Utils;
