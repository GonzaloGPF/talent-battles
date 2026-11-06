<template>
  <app-button
    v-if="!model?.product && user.is_account_ready"
    :disabled="!! model?.product"
    :label="title"
    :loding="loading"
    :modal="modal"
    color="green"
    prepend-icon="$sale"
    @click="showModal = true"
  >
    <template #modal>
      <product-form
        ref="formElement"
        :model="model"
      />
    </template>
  </app-button>
</template>
<script setup>
import ProductForm from '@/components/forms/ProductForm.vue';
import useHttp from '@/composables/useHttp';
import { computed, ref } from 'vue';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';

const emits = defineEmits(['success']);
const props = defineProps({
    model: {
        type: Object,
        required: true,
        default: null
    },
});
const { loading, postJson } = useHttp();
const { user } = storeToRefs(useAuth());
const formElement = ref(null);
const showModal = ref(false);
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

const title = computed(() => Translator.actionTitle('sell'));
const onSubmit = () => {
    const valid = formElement.value?.validate(true);

    if (! valid) return;

    valid.append('model_type', props.model.model);
    valid.append('model_id', props.model.id);

    postJson('products', valid)
        .then(() => {
          showModal.value = false;
          emits('success');
        });
}
</script>
