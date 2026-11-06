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

    <i-select
      v-if="! hideRole"
      v-model="form.role"
      :errors="errors('role')"
      name="role"
      src="roles"
    />
  </app-form>
</template>
<script setup>
import useFormValidator from '@/composables/useFormValidator';
import rules from '@/components/forms/rules/userRules';
import IText from '@/components/forms/inputs/iText.vue';
import ISelect from '@/components/forms/inputs/iSelect.vue';
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

const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, ['name', 'email', 'role']);

defineExpose({ validate, reset, setForm });
</script>
