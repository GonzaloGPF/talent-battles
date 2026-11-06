import { numeric, required, requiredIf } from '@vuelidate/validators';

export default {
    track_type: {
        required,
    },
    name: {
        required,
    },
    audio: {
        required: requiredIf((value, form) => ! isCreating(form))
    },
    // image: {
    //     required: requiredIf((value, form) => ! isCreating(form))
    // },
    file_to_sell: {
        required: requiredIf((value, form) => form.sell && ! form.auto_tag)
    },
    price: {
        required: requiredIf((value, form) => form.sell),
        numeric
    },
}

/**
 *
 * @param form
 * @returns {boolean}
 */
function isCreating(form) {
    return !! form?.id
}
