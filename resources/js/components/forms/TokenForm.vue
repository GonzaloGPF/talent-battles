<template>
  <app-form>
    <i-text
      v-model="form.name"
      :errors="errors('name')"
      name="name"
      class="max-w-sm"
    />
  </app-form>
</template>
<script setup>
import { required } from '@vuelidate/validators';
import useFormValidator from '@/composables/useFormValidator';
import IText from '@/components/forms/inputs/iText.vue';
import AppForm from '@/components/core/AppForm.vue';

const rules = {
    name: { required }
};
const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    },
});
const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, ['name']);

defineExpose({ validate, reset, setForm });
</script>
