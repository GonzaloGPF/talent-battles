<template>
  <app-form>
    <i-password
      v-model="form.password"
      :errors="errors('password')"
      name="password"
    />

    <i-password
      v-model="form.password_confirmation"
      :errors="errors('password_confirmation')"
      name="password_confirmation"
    />
  </app-form>
</template>
<script setup>
import useFormValidator from '@/composables/useFormValidator';
import rules from '@/components/forms/rules/passwordResetRules';
import AppForm from '@/components/core/AppForm.vue';
import IPassword from '@/components/forms/inputs/iPassword.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, [
    'password',
    'password_confirmation',
]);

defineExpose({ validate, reset, setForm });
</script>
