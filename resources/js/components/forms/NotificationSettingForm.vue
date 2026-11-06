<template>
  <app-form class="w-100">
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
          v-for="(notificationSetting, notificationName) in form"
          :key="notificationName"
        >
          <td v-text="Translator.t(`notifications.${notificationName}`)" />
          <td v-if="form[notificationName]">
            <i-switch
              v-model="form[notificationName]['broadcast']"
              :label="null"
              hide-details
              inline
              name="broadcast"
              class="notification-table-input"
            />
          </td>
          <td v-if="form[notificationName]">
            <i-switch
              v-model="form[notificationName]['mail']"
              :label="null"
              hide-details
              inline
              name="mail"
              class="notification-table-input"
            />
          </td>
        </tr>
      </tbody>
    </v-table>
  </app-form>
</template>
<script setup>
import useFormValidator from '@/composables/useFormValidator';
import Translator from '@/objects/Translator';
import ISwitch from '@/components/forms/inputs/iSwitch.vue';
import AppForm from '@/components/core/AppForm.vue';
import { watch } from 'vue';

const emits = defineEmits(['update:modelValue']);
const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const { form, validate, reset, setForm } = useFormValidator(props.modelValue, [], Object.keys(props.modelValue));

watch(form, () => emits('update:modelValue', form));

defineExpose({ validate, reset, setForm });
</script>
