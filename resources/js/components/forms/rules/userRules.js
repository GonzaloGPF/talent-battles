import { email, required } from '@vuelidate/validators';

export default {
    name: {
        required
    },
    email: {
        required,
        email
    },
    role: {
        required
    }
}
