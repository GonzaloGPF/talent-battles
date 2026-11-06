<template>
  <v-container v-if="product && isMine" fluid>
    <app-alert
      :message="Translator.t('help.product_file')"
      variant="tonal"
      class="mb-2 max-w-md"
      density="compact"
    />
    <div class="d-flex align-center space-x-2">
      <app-download
        v-if="product?.file"
        :file="product?.file"
        color="blue"
        with-text
      />
      <app-button
        :label="Translator.actionTitle('delete')"
        :loding="loading"
        size="small"
        color="red"
        variant="outlined"
        @click="onClick"
      />
    </div>
  </v-container>
  <create-product-button
    v-else
    :model="model"
    @success="$emit('success')"
  />
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppAlert from '@/components/core/AppAlert.vue';
import AppButton from '@/components/core/AppButton.vue';
import useHttp from '@/composables/useHttp';
import { useConfirm } from '@/stores/confirm';
import CreateProductButton from '@/components/buttons/CreateProductButton.vue';
import { computed } from 'vue';
import AppDownload from '@/components/core/AppDownload.vue';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';

const emits = defineEmits(['success']);
const props = defineProps({
    model: {
        type: Object,
        default: null
    }
});
const { deleteJson, loading } = useHttp();
const { user } = storeToRefs(useAuth());
const { showConfirm } = useConfirm();

const product = computed(() => props.model?.product);
const isMine = computed(() => props.model?.created_by === user?.value?.id);
const onClick = async () => {
  const accept = await showConfirm(Translator.t('help.delete_product'));

  if (! accept) return;

  deleteJson(`products/${props.model?.product?.id}`)
    .then(() => emits('success'));
}
</script>
