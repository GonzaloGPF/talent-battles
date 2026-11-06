<template>
  <app-form>
    <i-text
      v-model="form.name"
      :errors="errors('name')"
      name="name"
    />

    <i-text
      v-model="form.email"
      :errors="errors('email')"
      name="email"
    />
  </app-form>
</template>
<script setup>
import useFormValidator from '@/composables/useFormValidator';
import rules from '@/components/forms/rules/invitationRules';
import IText from '@/components/forms/inputs/iText.vue';
import AppForm from '@/components/core/AppForm.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    },
    hideRole: {
        type: Boolean,
        default: false
    },
});

const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, ['name', 'email']);

defineExpose({ validate, reset, setForm });
</script>
