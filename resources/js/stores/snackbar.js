import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';

export const useSnackbar = defineStore('snackbar', () => {
    const snackbarMessage = ref('');
    const isShowing = ref(false);
    const snackParams = reactive({
        timeout: -1,
        rounded: false,
        variant: 'elevated', // 'text' | 'flat' | 'elevated' | 'tonal' | 'outlined' | 'plain'
        multiline: false,
        color: undefined,
        location: 'bottom', // top, bottom, right, left, can be combined like for example top right,
        title: null,
    });
    const actionButton = reactive({
        color: null,
        icon: null,
        title: null,
        label: null,
        loading: null,
        onClick: null
    });

    const showSnackbar = (message, customParams = null, buttonParams = null) => {
        if (! message) return;

        setSnackParams(customParams);
        setButtonAction(buttonParams);

        snackbarMessage.value = message;
        isShowing.value = true;
    }

    const clean = () => {
        snackbarMessage.value = null;
        isShowing.value = false;
    }

    const setSnackParams = (customParams) => {
        snackParams.timeout = customParams?.timeout ?? snackParams.timeout;
        snackParams.multiline = customParams?.multiline ?? snackParams.multiline;
        snackParams.rounded = customParams?.rounded ?? snackParams.rounded;
        snackParams.variant = customParams?.variant ?? snackParams.variant;
        snackParams.title = customParams?.title ?? snackParams.title;
    }

    const setButtonAction = (buttonParams) => {
        actionButton.color = buttonParams?.color ?? actionButton.color;
        actionButton.icon = buttonParams?.icon ?? actionButton.icon;
        actionButton.title = buttonParams?.title ?? actionButton.title;
        actionButton.label = buttonParams?.label ?? actionButton.label;
        actionButton.loading = buttonParams?.loading ?? actionButton.loading;
        actionButton.onClick = buttonParams?.onClick ?? actionButton.onClick;
    }

    return {
        showSnackbar,
        clean,
        isShowing,
        snackbarMessage,
        snackParams,
        actionButton,
    }
});
