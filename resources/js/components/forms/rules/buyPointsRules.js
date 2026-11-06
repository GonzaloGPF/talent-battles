import { minValue, required } from '@vuelidate/validators';

export default {
    points: {
        required,
        minValue: minValue(50)
    },
}
