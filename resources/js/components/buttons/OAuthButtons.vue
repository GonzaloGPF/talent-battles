<template>
  <field-block
    :title="Translator.t('help.external_login')"
    class="text-start"
  >
    <app-buttons
      :buttons="['google', 'facebook']"
      class="mt-3"
      size="small"
      @click="doOpenTab(`${$event}/redirect`)"
    />
  </field-block>
</template>
<script setup>
import Translator from '@/objects/Translator';
import Utils from '@/objects/Utils';
import FieldBlock from '@/components/core/FieldBlock.vue';
import AppButtons from '@/components/core/AppButtons.vue';

const emit = defineEmits(['redirected']);
const doOpenTab = (path) =>{
    Utils.openTab(path);
    emit('redirected');
}
</script>
