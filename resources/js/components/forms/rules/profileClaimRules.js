import { email, required } from '@vuelidate/validators';

export default {
    email: {
        required,
        email
    },
    password: {
        required,
    },
    password_confirmation: {
        required,
    },
    terms_and_conditions: {
        required
    },
}
