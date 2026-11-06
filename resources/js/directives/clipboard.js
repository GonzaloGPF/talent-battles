import Translator from '@/objects/Translator';

export default {
    beforeMount: (el, binding) => {
        if (! window.isSecureContext) { // Only in https context
            el.classList.add('d-none');
            return;
        }

        el.setAttribute('title', Translator.t('help.click_to_copy'));

        el.copyToClipboard = () => navigator.clipboard.writeText(binding.value);

        el.addEventListener('click', el.copyToClipboard);
    },
    unmounted: el => {
        el.removeEventListener('click', el.copyToClipboard);
    },
}
