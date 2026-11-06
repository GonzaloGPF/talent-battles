<template>
  <span>
    <span v-text="Translator.t('help.legal_text_1')" />

    <a
      :href="conditionUrl"
      target="_blank"
      class="mx-1 text-blue hover:underline"
      v-text="Translator.tl('terms_and_conditions')"
    />

    <span v-text="Translator.t('help.legal_text_2')" />

    <a
      :href="privacyUrl"
      target="_blank"
      class="mx-1 text-blue hover:underline"
      v-text="Translator.tl('privacy_policy')"
    />
    <span v-text="Translator.t('help.legal_text_3', { app_name: global.getAppName() })" />
  </span>
</template>
<script setup>
import global from '@/config/global';
import Translator from '@/objects/Translator';
import Utils from '@/objects/Utils';

const conditionUrl = Utils.getFullUrl(`docs/${ Translator.getLocale() }/conditions`);
const privacyUrl = Utils.getFullUrl(`docs/${ Translator.getLocale() }/privacy`);
</script>
