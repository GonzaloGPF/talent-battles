<template>
  <div>
    <docs-button
      path="account"
      class="float-right"
    />

    <account-alerts
      class="mb-5 max-w-md"
      density="default"
    />

    <account-buttons
      class="mb-3"
      @success="fetchMe"
    />

    <account-info
      :model-value="user.account"
      class="mb-2"
    />

    <address-editor />
  </div>
</template>
<script setup>
import AccountAlerts from '@/components/alerts/AccountAlerts.vue';
import AccountButtons from '@/components/buttons/AccountButtons.vue';
import { useAuth } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import AccountInfo from '@/components/info/AccountInfo.vue';
import AddressEditor from '@/components/editors/AddressEditor.vue';
import DocsButton from '@/components/buttons/DocsButton.vue';

const { fetchMe } = useAuth();
const { user } = storeToRefs(useAuth());
</script>
