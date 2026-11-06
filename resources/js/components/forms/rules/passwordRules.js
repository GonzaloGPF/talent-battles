import { required } from '@vuelidate/validators';

export default {
    current_password: {
        required,
    },
    password: {
        required,
    },
    password_confirmation: {
        required,
        // sameAs: sameAs('password')
    },
}
