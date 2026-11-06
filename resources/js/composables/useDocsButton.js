import { computed, ref } from 'vue';
import Translator from '@/objects/Translator';
import Utils from '@/objects/Utils';

export default (path) => {
    path = ref(path);

    const docsPath = computed(() => {
        const locale = Translator.getLocale();

        return `docs/${locale}/${path.value}`;
    });

    const goToDoc = () => Utils.openTab(docsPath.value);

    return {
        docsPath,
        goToDoc
    }
}
