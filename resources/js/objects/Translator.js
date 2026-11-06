import { getActiveLanguage, I18n, loadLanguageAsync, trans, transChoice } from 'laravel-vue-i18n';
import Formatter from '@/objects/Formatter';

const Translator = {
    getLocale: () => getActiveLanguage(),

    setLocale(locale) {
        if (! locale) return;

        document.documentElement.setAttribute('lang', locale);

        return loadLanguageAsync(locale);
    },

    t: (string, options = {}) => trans(string, options),

    /**
     * Translates an attribute
     *
     * @param {string} attribute
     * @returns {string}
     */
    ta: (attribute) => {
        if (! attribute) return '';

        return Translator.t(`validation.attributes.${ attribute }`);
    },

    /**
     * Translates a label
     *
     * @param {string} label
     * @param {*} options
     * @returns {string}
     */
    tl: (label, options = {}) => {
        if (! label) return '';

        return Translator.t(`labels.${ label }`, options);
    },

    /**
     * Translate
     *
     * @param {string} path
     * @param {number} choice
     * @returns {string}
     */
    tc: (path, choice = 1) => transChoice(path, choice),

    /**
     * Checks if given translation exists
     *
     * @param {string} path
     */
    te: (path) => {
        const translations = Object.keys(I18n.getSharedInstance().activeMessages || {});
        return translations.includes(path);
    },

    /**
     * Translates a Constant value
     *
     * @param constant
     * @param {string} value
     * @returns {string}
     */
    tConstName: (constant, value) => {
        if (! value) return '';

        constant = Formatter.plural(Formatter.snakeCase(constant));

        return Translator.t(`constants.${ constant }.${ value.toLowerCase().trim() }`);
    },

    /**
     * Tries to translate a value looking for everywhere posible
     * @param {string} string
     * @param {boolean} plural
     * @returns {string}
     */
    translate: (string, plural = false) => {
        if (Translator.te(`labels.${ string }`)) {
            return Translator.tl(string);
        }

        if (Translator.te(`validation.attributes.${ string }`)) {
            return Translator.ta(string);
        } else {
            let singularString = Formatter.singular(string);
            if (Translator.te(`validation.attributes.${ singularString }`)) {
                return Translator.ta(singularString);
            }
        }

        let modelName = Formatter.singular(Formatter.snakeCase(string));

        if (Translator.te(`models.${ modelName }`)) {
            return Translator.modelTitle(modelName, plural);
        }

        return Formatter.ucFirst(string);
    },

    /**
     * Translates an Enum
     *
     * @returns {string}
     * @param model
     * @param plural
     */
    constantTitle: (model, plural = false) => {
        if (! model) return '';

        let snakeModel = Formatter.snakeCase(model);
        let singularModel = Formatter.singular(snakeModel);

        return Translator.tc(`constants.${ singularModel }.${ singularModel }`, plural ? 2 : 1);
    },

    /**
     * Translates a model name.
     *
     * @param {string} model
     * @param {boolean} plural
     * @returns {string}
     */
    modelTitle: (model, plural = false) => {
        if (! model) return '';

        const snakeModel = Formatter.snakeCase(model);
        const singularModel = Formatter.singular(snakeModel);

        return Translator.tc(`models.${ singularModel }`, plural ? 2 : 1);
    },

    /**
     * Translation for a specific verb and model
     *
     * @param {string} action
     * @param female
     * @param {string|null} model
     * @param plural
     * @returns {string}
     */
    actionTitle: (action, model = null, female = null, plural = false) => {
        let translatedAction;

        if (female === null) {
            translatedAction = Translator.t(`actions.${ action }`);
        } else {
            translatedAction = Translator.tc(`actions.${ action }`, female ? 2 : 1);
        }

        if (model) {
            const translatedModel = Translator.modelTitle(model, plural);
            translatedAction = female === null
                ? `${ translatedAction } ${ translatedModel }`
                : `${ translatedModel } ${ translatedAction }`;
        }

        return translatedAction;
    },
}

export default Translator;
