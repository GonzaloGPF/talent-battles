<template>
  <app-button
    :label="title"
    :color="null"
    :modal="modal"
    class="w-100"
    prepend-icon="$favorite"
    size="small"
    @click="showModal = true"
  >
    <template #menu>
      <admin-favorite-lists @success="$emit('success')" />
    </template>
  </app-button>
</template>
<script setup>
import AdminFavoriteLists from '@/components/admin/AdminFavoriteLists.vue';
import { computed, ref } from 'vue';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';

defineEmits(['success']);

const showModal = ref(false);
const title = computed(() => Translator.modelTitle('favoriteList', true));
const modal = computed(() => ({
  data: {
    modelValue: showModal.value,
    title: title.value,
  },
  listeners: {
    close: () => showModal.value = false,
    ok: () => showModal.value = false,
  }
}));
</script>
