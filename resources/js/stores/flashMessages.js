import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

const MESSAGE_TTL = 3000;

export const useFlashMessages = defineStore('flashMessages', () => {
    const flashMessages = ref([]);

    const hasMessages = computed(() => flashMessages.value?.length)

    /**
     * {
     *   title: String
     *   message: String - Required,
     *   icon: String,
     *   border: String,
     *   closable: Boolean,
     *   type: String ('success', 'info', 'warning', or 'error')
     *   duration: Number,
     *   variant: String (elevated, flat, tonal, outlined, text, and plain). Default is Flat
     * }
     * @param message {
     *      title: string|null,
     *      message: string|null,
     *      icon: string|null,
     *      border: string|null,
     *      type: string,
     *      duration: int|null,
     *      variant: string|null
     * }
     * @param params
     */
    const pushFlashMessage = (message, params = {}) => {
        if (! message) return;

        const flashMessage = addMessage(parseFlashMessage(message, params));

        if (! flashMessage) return;

        flashMessage.timer = setTimeout(() => destroy(flashMessage), flashMessage.duration);
    }

    const addMessage = (flashMessage) => {
        if (isDuplicated(flashMessage)) {
            return;
        }

        flashMessages.value.push(flashMessage);

        return flashMessage;
    }

    const isDuplicated = (flashMessage) => flashMessages.value.find(iMessage => iMessage.message === flashMessage.message);

    const destroy = (flashMessage) => {
        clearTimeout(flashMessage.timer);

        flashMessage.destroyed = true;

        clean();
    }

    const clean = () => {
        flashMessages.value = flashMessages.value.filter(flashMessage => ! flashMessage.destroyed);
    }

    const parseFlashMessage = (message, params = {}) => {
        if (typeof message === 'string') {
            params.message = message;
        } else {
            params = message || {};
        }

        return {
            title: params.title,
            message: params.message,
            icon: params.icon,
            closable: params.closable,
            type: params.type,
            duration: params.duration || MESSAGE_TTL,
            variant: params.variant,
        }
    }

    return {
        hasMessages,
        flashMessages,
        pushFlashMessage,
    }
});
