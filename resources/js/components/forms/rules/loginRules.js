import { email, required } from '@vuelidate/validators';

export default {
    email: {
        required,
        email
    },
    password: {
        required
    }
}
