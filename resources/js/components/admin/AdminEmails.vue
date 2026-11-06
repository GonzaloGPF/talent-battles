<template>
  <div>
    <data-table
      :headers="headers"
      :filter-fields="filters"
      :title="Translator.modelTitle('email', true)"
      :actions="buttons"
      :params="tableParams"
      :searchable="true"
      url="emails"
      @view="Utils.openTab(`emails/${ $event.id }/render`)"
      @send="onSend"
    />
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/emailHeaders';
import filters from '@/components/filters/emailFilters';
import Translator from '@/objects/Translator';
import useHttp from '@/composables/useHttp';
import { computed } from 'vue';
import { useAuth } from '@/stores/auth';
import Utils from '@/objects/Utils';
import { useConfirm } from '@/stores/confirm';
import { storeToRefs } from 'pinia';
import DataTable from '@/components/tables/DataTable.vue';

const props = defineProps({
    params: {
        type: Object,
        default: null
    }
})
const { showConfirm } = useConfirm();
const { putJson } = useHttp();
const { isInternal } = storeToRefs(useAuth());
const tableParams = computed(() => ({
    with: ['createdBy'],
    ...props.params
}))

const buttons = computed(() => {
    const buttons = ['view'];

    if (isInternal.value) {
        buttons.push('send');
    }

    return buttons;
});

async function onSend(email) {
    const accept = await showConfirm(Translator.t('help.resend_email'));

    if (! accept) return;

    putJson(`emails/${ email.id }/resend`);
}
</script>
