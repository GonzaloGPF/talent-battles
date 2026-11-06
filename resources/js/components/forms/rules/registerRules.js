import { email, required } from '@vuelidate/validators';

export default {
    name: {
        required
    },
    email: {
        required,
        email
    },
    password: {
        required,
    },
    password_confirmation: {
        required,
        // sameAs: sameAs('password')
    },
    terms_and_conditions: {
        required
    },
}
