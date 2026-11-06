import IBAN from 'iban';
import Time from '@/objects/Time';

/**
 * Custom Validations for Async-Validator https://github.com/yiminghe/async-validator
 * Each validation receive rule, value and callback params.
 * Messages and it's translations will be linked by FormMixin
 */
export default {
    /**
     * Check if given value is a valid IBAN
     *
     * @param rule
     * @param value
     * @param callback
     */
    isIban(rule, value, callback) {
        let errors = [];

        if (value && !IBAN.isValid(value)) {
            errors.push(new Error('Invalid Iban'));
        }

        callback(errors);
    },

    /**
     * Checks if given value is a Date
     *
     * @param rule
     * @param value
     * @param callback
     */
    isDate(rule, value, callback) {
        let errors = [];

        if (! Time.parse(value)) {
            errors.push(new Error('Invalid Date'))
        }

        callback(errors);
    },

    /**
     * Validates if the given Date is after Today's Date
     *
     * @param rule
     * @param value
     * @param callback
     */
    isAfter(rule, value, callback) {
        let errors = [];

        if (! Time.isAfter(value, Time.now())) {
            errors.push(new Error('Not allowed Date'));
        }

        callback(errors);
    },
}
