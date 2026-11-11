<template>
  <field-block
    :title="Translator.t('help.external_login')"
    class="text-start"
  >
    <app-buttons
      :buttons="['google', 'facebook']"
      class="mt-3"
      size="small"
      @click="doLogin"
    />
  </field-block>
</template>
<script setup>
import Translator from '@/objects/Translator';
import FieldBlock from '@/components/core/FieldBlock.vue';
import AppButtons from '@/components/core/AppButtons.vue';
import useOAuthLogin from '@/composables/useOAuthLogin';

const emit = defineEmits(['redirected']);
const { loginWith } = useOAuthLogin();
const doLogin = (driver) => {
    emit('redirected');
    loginWith(driver);
}
</script>
