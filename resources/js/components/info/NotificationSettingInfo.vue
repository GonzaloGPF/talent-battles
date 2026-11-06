<template>
  <v-table>
    <thead>
      <tr>
        <th v-text="Translator.ta('name')" />
        <th v-text="Translator.modelTitle('notification')" />
        <th v-text="Translator.ta('mail')" />
      </tr>
    </thead>
    <tbody>
      <tr
        v-for="(notificationSetting, notificationName) in (modelValue || {})"
        :key="notificationName"
      >
        <td v-text="Translator.t(`notifications.${notificationName}`)" />
        <td>
          <app-field
            :model-value="notificationSetting.broadcast"
            type="boolean"
          />
        </td>
        <td>
          <app-field
            :model-value="notificationSetting.mail"
            type="boolean"
          />
        </td>
      </tr>
    </tbody>
  </v-table>
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppField from '@/components/core/AppField.vue';

defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
</script>
