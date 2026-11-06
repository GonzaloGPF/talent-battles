<template>
  <v-container fluid>
    <v-progress-linear
      :active="loading"
      indeterminate
    />

    <complete-order-button
      :order="vModel"
      class="mb-2 max-w-sm"
      @success="$emit('success')"
    />

    <order-info :model-value="vModel" />
  </v-container>
</template>
<script setup>
import OrderInfo from '@/components/info/OrderInfo.vue';
import CompleteOrderButton from '@/components/buttons/CompleteOrderButton.vue';
import useDetails from '@/composables/useDetails';

defineEmits(['success']);
const props = defineProps({
    modelId: {
        type: Number,
        default: null
    },
    paymentIntentId: {
        type: String,
        default: null
    }
});
const { loading, vModel } = useDetails(props.modelId, 'orders', {
    payment_intent_id: props.paymentIntentId,
    with: [
        'createdBy',
        'orderLines',
        'orderLines.createdBy',
        'orderLines.seller',
        'orderLines.orderable',
        'orderLines.file',
    ],
    counts: [
        'orderLines'
    ]
});
</script>
