import { computed, nextTick, ref } from 'vue';
import { useRouter } from 'vue-router';
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import { useConfirm } from '@/stores/confirm';
import Formatter from '@/objects/Formatter';
import Utils from '@/objects/Utils';
import FormValidator from '@/objects/FormValidator';

export default (model, tableElement, formElement, asFormData) => {
    const { push } = useRouter();
    const { showConfirm } = useConfirm();
    const { postJson, putJson, deleteJson, downloadFile, loading } = useHttp();

    const showModal = ref(false);
    const modelId = ref(null);
    const title = computed(() => Translator.modelTitle(model, true));
    const modalTitle = computed(() => Translator.actionTitle(modelId.value ? 'edit' : 'create', model));
    const baseUrl = Formatter.plural(Formatter.snakeCase(model));

    function onCreate() {
        showModal.value = true;
        modelId.value = null;
        formElement.value?.reset();
    }

    function onEdit(item) {
        showModal.value = true;
        modelId.value = item.id;
        return nextTick(() => {
            const dataWithoutFiles = Utils.copy(item, FormValidator.fileInputs());
            formElement.value?.setForm(dataWithoutFiles);
        });
    }

    function onDownload(item) {
        downloadFile(item.file);
    }

    function onView(item) {
        return push({
            name: `${ baseUrl }.show`,
            params: {
                id: item.id,
            }
        });
    }

    function onClose() {
        showModal.value = false;
        modelId.value = null;
        formElement.value?.reset()
    }

    async function onDelete(item) {
        if (loading.value) return;

        if (item.deleted_at) return onRestore(item); // TODO: ask about force delete

        const accept = await showConfirm(Translator.t('help.delete_text'));

        if (! accept) return;

        return deleteJson(`${ baseUrl }/${ item.id }`)
            .then(onSuccess);
    }

    async function onRestore(item) {
        if (loading.value) return;

        const result = await showConfirm(Translator.t('help.restore_text'));

        if (! result) return;

        return postJson('restore', {
            model_type: Formatter.singular(model),
            model_id: item.id
        }).then(onSuccess)
    }

    function onSubmit() {
        if (loading.value) return;

        const data = formElement.value?.validate(asFormData);

        if (! data) {
            return null;
        }

        if (modelId.value) {
            return update(data);
        } else {
            return create(data);
        }
    }

    function create(data) {
        return postJson(baseUrl, data)
            .then(onSuccess)
    }

    function update(data, customUrl) {
        let url = customUrl ?? `${ baseUrl }/${ modelId.value }`;

        if (data instanceof FormData) {
            data.append('_method', 'PUT');
            return postJson(url, data)
                .then(onSuccess);
        }

        return putJson(url, data)
            .then(onSuccess);
    }

    function onSuccess() {
        showModal.value = false;
        modelId.value = null
        formElement.value?.reset();
        tableElement.value?.refresh();

        return new Promise(resolve => resolve())
        // this.refreshTable();
        // this.$emit('success')
    }

    function refresh() {
        tableElement.value?.refresh();
    }

    return {
        title,
        modalTitle,
        showModal,
        loading,
        refresh,
        create,
        update,
        onView,
        onCreate,
        onEdit,
        onDelete,
        onDownload,
        onSubmit,
        onClose,
        onSuccess,
    }
}
