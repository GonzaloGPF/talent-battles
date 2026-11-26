<template>
  <div>
    <docs-button
      path="cart"
      class="position-absolute right-20"
    />
    <v-progress-linear
      :active="loading"
      indeterminate
    />
    <h3
      class="text-h6 brand-font text-center"
      v-text="Translator.modelTitle('cart')"
    />
    <div
      v-if="cartItems.length"
      class="flex flex-col md:items-center"
    >
      <app-alert
        :message="Translator.t('help.going_to_do_a_purchase')"
        class="my-4 md:my-8 md:min-w-md"
      />

      <simple-table
        :items="cartItems"
        :headers="headers"
        :actions="getItemAction"
        :with-sum="['total_with_taxes']"
        @view="openItem"
        @delete="removeCartItems"
      />

      <div class="flex flex-col gap-2 mt-6 md:max-w-md">
        <div class="flex items-center gap-2">
          <i-switch
            v-model="termsAccepted"
            :label="null"
            hide-details
            name="terms_and_conditions"
            class="flex-shrink-0"
          />
          <legal-info />
        </div>
        <div
          v-if="needsImmediateDelivery"
          class="flex items-center gap-2"
        >
          <i-switch
            v-model="immediateDeliveryAccepted"
            :label="null"
            hide-details
            name="immediate_delivery"
            class="flex-shrink-0"
          />
          <span v-text="Translator.t('help.immediate_delivery')" />
        </div>
      </div>

      <validation-errors class="my-3 rounded max-w-lg" />

      <pay-now-button class="mt-6 md:max-w-md" />

      <payment-elements-button
        class="my-1 md:max-w-md"
        @loading="loading = $event"
      />

      <app-button
        :loading="loading"
        :label="Translator.actionTitle('remove')"
        color="error"
        variant="tonal"
        class="brand-font mt-6 md:max-w-md"
        @click="onRemoveAll"
      />
    </div>
    <app-empty
      v-else
      class="text-center mt-6"
    />
  </div>
</template>
<script setup>
import { storeToRefs } from 'pinia';
import { useCart } from '@/stores/cart';
import { useConfirm } from '@/stores/confirm';
import Translator from '@/objects/Translator';
import AppAlert from '@/components/core/AppAlert.vue';
import AppButton from '@/components/core/AppButton.vue';
import { ref } from 'vue';
import SimpleTable from '@/components/tables/SimpleTable.vue';
import headers from '@/components/tables/headers/cartItemHeaders';
import AppEmpty from '@/components/core/AppEmpty.vue';
import PaymentElementsButton from '@/components/buttons/PaymentElementsButton.vue';
import PayNowButton from '@/components/buttons/PayNowButton.vue';
import ValidationErrors from '@/components/core/ValidationErrors.vue';
import DocsButton from '@/components/buttons/DocsButton.vue';
import ISwitch from '@/components/forms/inputs/iSwitch.vue';
import LegalInfo from '@/components/info/LegalInfo.vue';

const { removeCartItems, clean, openItem } = useCart();
const { cartItems, termsAccepted, immediateDeliveryAccepted, needsImmediateDelivery } = storeToRefs(useCart());
const { showConfirm } = useConfirm();
const loading = ref(false);

const onRemoveAll = async () => {
    const accept = await showConfirm(Translator.t('help.delete_all'));

    if (! accept) return;

    clean();
}

function getItemAction(item) {
    if (['track', 'design'].includes(item.type)) {
        return ['view', 'delete'];
    }

    return ['delete'];
}
</script>