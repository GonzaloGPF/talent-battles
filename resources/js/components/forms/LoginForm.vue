<template>
  <app-form>
    <i-text
      v-model="form.email"
      :errors="errors('email')"
      :autocomplete="true"
      prepend-icon="mdi-email"
      name="email"
    />

    <i-password
      v-model="form.password"
      :errors="errors('password')"
      prepend-icon="$password"
      name="password"
    />
  </app-form>
</template>
<script setup>
import rules from '@/components/forms/rules/loginRules';
import useFormValidator from '@/composables/useFormValidator';
import IText from '@/components/forms/inputs/iText.vue';
import AppForm from '@/components/core/AppForm.vue';
import IPassword from '@/components/forms/inputs/iPassword.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});

const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, [
    'email',
    'password',
]);

defineExpose({ validate, reset, setForm });
</script>
