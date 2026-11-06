<template>
  <app-field
    v-if="files.length"
    :label="Translator.tl('attachments')"
    icon="paperclip"
  >
    <ul class="list-none" style="padding: 0">
      <li
        v-for="file in vFiles"
        :key="file.id"
        class="-mb-2 p-0"
      >
        <div
          v-if="showDates"
          class="text-xs mt-2"
          style="padding-left: 10px;"
        >
          <span
            class="font-semibold"
            v-text="file.createdBy?.nick_name"
          />
          <span
            v-text="`[ ${Formatter.date(file.created_at)} ]`"
          />
        </div>
        <div :class="showDates ? 'mb-3' : null">
          <app-button
            :label="Formatter.text(file.name, 20)"
            class="text-gray-600 hover:text-brand"
            variant="text"
            size="x-small"
            @click="downloadFile(file, downloable ? {} : { stream: true })"
          />
          <app-button
            v-if="deletable"
            :title="Translator.t('actions.delete')"
            :loading="loading"
            icon="trash"
            variant="text"
            @click="onDelete(file)"
          />
        </div>
      </li>
    </ul>
  </app-field>
</template>
<script setup>
import { computed, ref } from 'vue';
import useHttp from '@/composables/useHttp';
import AppButton from '@/components/core/AppButton.vue';
import { useConfirm } from '@/stores/confirm';
import Formatter from '@/objects/Formatter';
import Translator from '@/objects/Translator';
import AppField from '@/components/core/AppField.vue';

const emits = defineEmits(['success']);
const props = defineProps({
  files: {
    type: Array,
    default: () => []
  },
  deletable: {
    type: Boolean,
    default: false
  },
  downloable: {
    type: Boolean,
    default: false
  },
  showDates: {
    type: Boolean,
    default: false
  }
});
const { downloadFile, deleteJson } = useHttp();
const { showConfirm } = useConfirm();
const loading = ref(false);
const vFiles = computed(() => props.files?.filter(file => file)); // filter null values

const onDelete = async file => {
  const confirm = await showConfirm();

  if (! confirm) return;

  deleteJson(`files/${ file.id }`)
    .then(() => emits('success'))
}
</script>
