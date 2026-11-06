import { required } from '@vuelidate/validators';

export default {
    password: {
        required,
    },
    password_confirmation: {
        required,
        // sameAs: sameAs('password')
    }
}
