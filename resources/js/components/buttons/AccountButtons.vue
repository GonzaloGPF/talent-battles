<template>
  <v-row>
    <v-col
      :cols="12"
      :md="6"
      :lg="4"
      :xl="3"
    >
      <activate-account-button @success="$emit('success')" />
      <create-account-link-button />
    </v-col>
    <v-col
      :cols="12"
      :md="6"
      :lg="4"
      :xl="3"
    >
      <billing-portal-button />
    </v-col>
  </v-row>
</template>
<script setup>
import ActivateAccountButton from '@/components/buttons/ActivateAccountButton.vue';
import CreateAccountLinkButton from '@/components/buttons/CreateAccountLinkButton.vue';
import BillingPortalButton from '@/components/buttons/BillingPortalButton.vue';

defineEmits(['success']);
</script>
