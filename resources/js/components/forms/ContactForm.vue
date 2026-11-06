<template>
  <app-form>
    <app-alert
      :message="Translator.t('help.write_contact_message')"
      variant="tonal"
      class="mb-2"
      density="compact"
    />
    <i-text
      v-if="!isLogged"
      v-model="form.email"
      :errors="errors('email')"
      name="email"
    />
    <i-text-area
      v-model="form.message"
      :errors="errors('message')"
      name="message"
      class="mb-3"
    />
  </app-form>
</template>
<script setup>
import rules from '@/components/forms/rules/contactRules';
import useFormValidator from '@/composables/useFormValidator';
import ITextArea from '@/components/forms/inputs/iTextArea.vue';
import IText from '@/components/forms/inputs/iText.vue';
import AppForm from '@/components/core/AppForm.vue';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';
import Translator from '@/objects/Translator';
import AppAlert from '@/components/core/AppAlert.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  },
});
const { isLogged } = storeToRefs(useAuth());
const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, [
  'email',
  'message',
]);

defineExpose({ validate, reset, setForm });
</script>
