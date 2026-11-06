<template>
  <app-button
    :title="Translator.actionTitle('comment')"
    :color="isActive ? 'green' : null"
    :disabled="! isLogged"
    :label="count || '0'"
    :modal="modal"
    stacked
    density="compact"
    icon="mdi-comment"
    size="small"
    variant="text"
    @click="showModal = true"
  >
    <template #modal>
      <comments-manager
        :model="vModel"
        hide-title
      />
    </template>
  </app-button>
</template>
<script setup>
import CommentsManager from '@/components/modules/comments/CommentsManager.vue';
import { computed, ref, toRef, watch } from 'vue';
import { useAuth } from '@/stores/auth';
import AppButton from '@/components/core/AppButton.vue';
import Translator from '@/objects/Translator';
import { storeToRefs } from 'pinia';

const props = defineProps({
    model: {
        type: Object,
        default: null,
        required: true,
    },
});
const modelProps = toRef(props, 'model');
const { isLogged } = storeToRefs(useAuth());
const showModal = ref(false);
const vModel = ref({});
const modal = computed(() => ({
  data: {
    modelValue: showModal.value,
    hideActions: true,
    title: Translator.modelTitle('comment', true),
  },
  listeners: {
    close: () => showModal.value = false,
  }
}));
const count = computed(() => vModel.value?.comments_count ||0);
const isActive = computed(() => !! vModel.value?.comment);
// const isMine = computed(() => user.id === vModel.value.created_by);

watch(modelProps, (value) => vModel.value = { ...value }, { immediate: true, deep: true });

</script>
