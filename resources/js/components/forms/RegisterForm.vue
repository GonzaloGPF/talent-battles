<template>
  <app-form>
    <i-text
      v-model="form.name"
      :errors="errors('name')"
      prepend-icon="mdi-account"
      name="name"
    />

    <i-text
      v-model="form.email"
      :errors="errors('email')"
      prepend-icon="mdi-email"
      name="email"
    />

    <i-password
      v-model="form.password"
      :errors="errors('password')"
      prepend-icon="$password"
      name="password"
    />

    <i-password
      v-model="form.password_confirmation"
      :errors="errors('password_confirmation')"
      prepend-icon="$password"
      name="password_confirmation"
    />

    <div class="d-flex flex-column flex-lg-row align-center">
      <i-switch
        v-model="form.terms_and_conditions"
        :errors="errors('terms_and_conditions')"
        :label="null"
        hide-details
        name="terms_and_conditions"
        style="width: 60px"
      />
      <legal-info />
    </div>
  </app-form>
</template>
<script setup>
import rules from '@/components/forms/rules/registerRules';
import LegalInfo from '@/components/info/LegalInfo.vue';
import useFormValidator from '@/composables/useFormValidator';
import IText from '@/components/forms/inputs/iText.vue';
import ISwitch from '@/components/forms/inputs/iSwitch.vue';
import AppForm from '@/components/core/AppForm.vue';
import IPassword from '@/components/forms/inputs/iPassword.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null,
    }
});
const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, [
    'name',
    'email',
    'password',
    'password_confirmation',
    'terms_and_conditions',
]);

defineExpose({ validate, reset, setForm });
</script>
