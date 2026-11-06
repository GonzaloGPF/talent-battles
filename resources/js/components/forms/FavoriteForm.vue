<template>
  <app-form>
    <i-auto-complete
      v-model="form.favorite_list_id"
      :errors="errors('favorite_list_id')"
      name="favorite_list_id"
      src="favorite_lists"
    />
  </app-form>
</template>
<script setup>
import IAutoComplete from "@/components/forms/inputs/iAutoComplete.vue";
import useFormValidator from "@/composables/useFormValidator";
import AppForm from '@/components/core/AppForm.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, {}, ['favorite_list_id']);

defineExpose({ validate, reset, setForm });
</script>
