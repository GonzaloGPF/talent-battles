import { required, requiredIf } from '@vuelidate/validators';
import { useAuth } from '@/stores/auth';

export default {
    email: {
        required: requiredIf(() => !useAuth().isLogged),
    },
    message: {
        required,
    },
}
