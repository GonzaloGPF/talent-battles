<template>
  <app-form>
    <i-text
      v-model="form.web"
      :errors="errors('web')"
      name="web"
    />

    <i-text
      v-model="form.facebook"
      :errors="errors('facebook')"
      name="facebook"
    />

    <i-text
      v-model="form.instagram"
      :errors="errors('instagram')"
      name="instagram"
    />

    <i-text
      v-model="form.youtube"
      :errors="errors('youtube')"
      name="youtube"
    />

    <i-text
      v-model="form.soundcloud"
      :errors="errors('soundcloud')"
      name="soundcloud"
    />

    <i-text
      v-model="form.soundclick"
      :errors="errors('soundclick')"
      name="soundclick"
    />

    <i-text
      v-model="form.twitter"
      :errors="errors('twitter')"
      name="twitter"
    />
  </app-form>
</template>
<script setup>
import { watch } from 'vue';
import useFormValidator from '@/composables/useFormValidator';
import rules from '@/components/forms/rules/socialRules';
import IText from '@/components/forms/inputs/iText.vue';
import AppForm from '@/components/core/AppForm.vue';

const emits = defineEmits(['update:modelValue']);
const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  }
});
const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, [
  'web',
  'facebook',
  'instagram',
  'soundcloud',
  'soundclick',
  'youtube',
  'twitter',
]);

watch(form, () => emits('update:modelValue', form));

defineExpose({ validate, reset, setForm });
</script>
