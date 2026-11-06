<template>
  <app-form hide-feedback>
    <i-text-area
      v-model="form.content"
      :label="Translator.actionTitle('comment')"
      :autofocus="true"
      :errors="errors('content')"
      name="content"
    />
  </app-form>
</template>
<script setup>
import rules from '@/components/forms/rules/commentRules';
import useFormValidator from "@/composables/useFormValidator";
import Translator from "@/objects/Translator";
import ITextArea from "@/components/forms/inputs/iTextArea.vue";
import AppForm from '@/components/core/AppForm.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, ['content']);

defineExpose({ validate, reset, setForm });
</script>
