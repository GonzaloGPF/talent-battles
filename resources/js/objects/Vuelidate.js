import { helpers } from '@vuelidate/validators';
import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';

const Vuelidate = {
    processRules(rules) {
        if (! rules) return;

        for (const [inputName, rulesBag] of Object.entries(rules)) {
            for (const [ruleName, rule] of Object.entries(rulesBag)) {
                rulesBag[ruleName] = helpers.withMessage((data) => Vuelidate.processMessage(ruleName, inputName, data), rule);
            }
        }

        return rules;
    },

    processMessage(ruleName, inputName, { $model, $params }) {
        return Vuelidate.getMessage(ruleName, inputName, $model, $params)
    },

    getMessage(ruleName, inputName, value, params) {
        let translatedInput;
        let laravelParams = {};
        if (! Translator.te(`validation.attributes.${inputName}`)) {
            translatedInput = Formatter.ucFirst(inputName);
        } else {
            translatedInput = Translator.ta(inputName);
        }

        if (ruleName === 'minValue') {
            ruleName = 'min.numeric';
            laravelParams = { min: params.min};
        }

        if (ruleName === 'sameAs') {
            ruleName = 'same';
            laravelParams = { other: Translator.ta(params.equalTo) }
        }

        return Translator.t(`validation.${ ruleName }`, { attribute: translatedInput, ...laravelParams });
    }
}

export default Vuelidate;
