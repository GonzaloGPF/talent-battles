<template>
  <app-form>
    <points-options @click="onClick" />
    <i-number
      ref="pointsElement"
      v-model="form.points"
      :errors="errors('points')"
      :min="50"
      name="points"
      class="max-w-xs mx-auto mt-3"
    />
  </app-form>
</template>
<script setup>
import rules from '@/components/forms/rules/buyPointsRules';
import useFormValidator from '@/composables/useFormValidator';
import { ref, watch } from 'vue';
import INumber from '@/components/forms/inputs/iNumber.vue';
import AppForm from '@/components/core/AppForm.vue';
import PointsOptions from '@/components/modules/points/PointsOptions.vue';

const emits = defineEmits(['update:modelValue']);
const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, ['points']);
const pointsElement = ref(null);

const onClick = (option) => {
    if (! pointsElement.value) return;

    form.points = option;
}

watch(form, () => emits('update:modelValue', form));

defineExpose({ validate, reset, setForm });
</script>
