<template>
  <app-form>
    <v-row>
      <v-col :cols="12" :md="6">
        <i-auto-complete
          v-model="form.country_id"
          :errors="errors('country_id')"
          name="country_id"
          src="countries"
          @upade:model-value="form.region_id = null"
        />
      </v-col>
      <v-col :cols="12" :md="6">
        <i-auto-complete
          v-model="form.region_id"
          :errors="errors('region_id')"
          :disabled="! form.country_id"
          :params="regionParams"
          name="region_id"
          src="regions"
        />
      </v-col>
      <v-col :cols="12" :md="6">
        <i-text
          v-model="form.city"
          :errors="errors('city')"
          name="city"
        />
      </v-col>
      <v-col :cols="12" :md="6">
        <i-text
          v-model="form.postcode"
          :errors="errors('postcode')"
          name="postcode"
        />
      </v-col>
    </v-row>
    <i-text
      v-model="form.line1"
      :errors="errors('line1')"
      name="line1"
    />
    <i-text
      v-model="form.line2"
      :errors="errors('line2')"
      name="line2"
    />
  </app-form>
</template>
<script setup>
import { computed, watch } from 'vue';
import useFormValidator from '@/composables/useFormValidator';
import IText from '@/components/forms/inputs/iText.vue';
import IAutoComplete from '@/components/forms/inputs/iAutoComplete.vue';
import AppForm from '@/components/core/AppForm.vue';
import addressRules from '@/components/forms/rules/addressRules';

const emits = defineEmits(['update:modelValue']);
const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});

const { form, errors, validate, reset, setForm } = useFormValidator(props.modelValue, addressRules, [
    'country_id',
    'region_id',
    'city',
    'postcode',
    'line1',
    'line2',
]);

const regionParams = computed(() => {
    if (! form.country_id) return null;

    return {
        country: [form.country_id]
    }
});

watch(form, () => emits('update:modelValue', form));

defineExpose({ validate, reset, setForm });

/*
if (this.form.latitude && this.form.longitude) {
    center.value = {
        lat: form.latitude * 1, // cast to number
        lng: form.longitude * 1
    };
    place.value = true;
}
*/
</script>
