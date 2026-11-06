<template>
  <app-button
    ref="buttonElement"
    :title="title"
    :loading="loading"
    :modal="modal"
    density="compact"
    icon="$email"
    size="small"
    variant="text"
    @click="showModal = true"
  >
    <template #modal>
      <ContactForm
        v-if="showModal"
        ref="formElement"
      />
    </template>
  </app-button>
</template>
<script setup>
import Translator from '@/objects/Translator';
import {computed, defineAsyncComponent, ref, toRefs, watch} from 'vue';
import useHttp from '@/composables/useHttp';
import Utils from '@/objects/Utils';
import AppButton from '@/components/core/AppButton.vue';
import { useRoute, useRouter } from 'vue-router';

const ContactForm = defineAsyncComponent(() => import('@/components/forms/ContactForm.vue'))

const { loading, postJson } = useHttp();
const { query } = toRefs(useRoute());
const showModal = ref(false);
const buttonElement = ref(null);
const title = computed(() => Translator.tl('contact'));
const formElement = ref();
const modal = computed(() => ({
  data: {
    modelValue: showModal.value,
    loading: loading.value,
    title: title.value,
  },
  listeners: {
    close: () => showModal.value = false,
    ok: onSubmit,
  }
}));

const onSubmit = () => {
  const validated = formElement.value?.validate();

  if (! validated || loading.value) {
    return;
  }

  return postJson('emails/contact', Utils.removeEmpty(validated))
    .then(onSuccess);
}

const onSuccess = () => {
  formElement.value?.reset();
  showModal.value = false;
}
const openModal = () => buttonElement.value?.click();

watch(query, (value) => {
  if (value.open_contact) {
    openModal();
    useRouter().replace(Utils.copy(value, ['open_contact']));
  }
});

defineExpose({
  openModal
});
</script>
