import { email, required, requiredIf } from '@vuelidate/validators';
import { useAuth } from '@/stores/auth';

export default {
    products: {
        required,
    },
    email: {
        required: requiredIf(() => ! useAuth().isLogged),
        email,
    },
}
