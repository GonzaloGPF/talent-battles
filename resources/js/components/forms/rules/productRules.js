import { numeric, required, requiredIf } from '@vuelidate/validators';

export default {
    file: {
        required: requiredIf((value, form) => !form.id)
    },
    price: {
        required,
        numeric
    },
}
