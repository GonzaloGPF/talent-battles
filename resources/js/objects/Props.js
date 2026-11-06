export const baseProps = {
    name: {
        type: String,
        required: true,
        default: null,
    },
    modelValue: {
        type: [Number, String, Boolean, Array, File, Object],
        default: null
    },
    label: {
        type: [String, Function],
        default: undefined
    },
    required: {
        type: Boolean,
        default: null
    },
    hint: {
        type: String,
        default: null
    },
    persistentHint: {
        type: Boolean,
        default: null
    },
    disabled: {
        type: Boolean,
        default: false
    },
    dense: {
        type: Boolean,
        default: false
    },
    outlined: {
        type: Boolean,
        default: false
    },
    autofocus: {
        type: Boolean,
        default: false
    },
    color: {
        type: String,
        default: null
    },
    placeholder: {
        type: String,
        default: null
    },
    tooltip: {
        type: String,
        default: null
    },
    prependIcon: {
        type: String,
        default: null
    },
    appendIcon: {
        type: String,
        default: null
    },
    rules: {
        type: Array,
        default: () => []
    },
    autocomplete: {
        type: Boolean,
        default: false
    },
    hideLabel: {
        type: Boolean,
        default: false
    },
    readonly: {
        type: Boolean,
        default: false
    },
    hideDetails: {
        type: Boolean,
        default: null
    },
    errors: {
        type: Array,
        default: () => []
    },
    title: {
        type: String,
        default: null
    }
}

export const textProps = {
    ...baseProps,
    type: {
        type: String,
        default: null
    },
    filled: {
        type: Boolean,
        default: null
    },
    clearable: {
        type: Boolean,
        default: null
    },
    singleLine: {
        type: Boolean,
        default: null
    },
    loading: {
        type: Boolean,
        default: null
    },
}

export const numberProps = {
    ...textProps,
    min: {
        type: Number,
        default: null
    },
    max: {
        type: Number,
        default: null
    },
}
