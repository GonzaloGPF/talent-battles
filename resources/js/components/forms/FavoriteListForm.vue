<template>
  <app-form>
    <i-text
      v-model="form.name"
      :errors="errors('name')"
      name="name"
    />
  </app-form>
</template>
<script setup>
import rules from '@/components/forms/rules/favoriteListRules';
import IText from "@/components/forms/inputs/iText.vue";
import useFormValidator from "@/composables/useFormValidator";
import AppForm from '@/components/core/AppForm.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, ['name']);

defineExpose({ validate, reset, setForm });
</script>
