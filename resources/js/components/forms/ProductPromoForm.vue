<template>
  <div>
    <v-window :model-value="step">
      <v-window-item>
        <data-table
          :headers="headers"
          :filter-fields="filters"
          :title="Translator.modelTitle('product', true)"
          :params="params"
          :show-select="true"
          :full-object="true"
          :expandable="false"
          url="products"
          @update:model-value="onProducts"
        />
      </v-window-item>
      <v-window-item>
        <app-form>
          <i-date
            :min="Formatter.date()"
            :show-current="false"
            :selected-items-text="Translator.t('help.total_months', { total: form.months || 0 })"
            hide-menu
            full-width
            range
            name="months"
            type="month"
            @update:model-value="onDate"
          />
        </app-form>
      </v-window-item>
      <v-window-item>
        <slot />
      </v-window-item>
    </v-window>
  </div>
</template>
<script setup>
import rules from '@/components/forms/rules/promoRules';
import DataTable from '@/components/tables/DataTable.vue';
import headers from '@/components/tables/headers/promoHeaders';
import filters from '@/components/filters/promoFilters';
import Translator from '@/objects/Translator';
import useFormValidator from '@/composables/useFormValidator';
import { computed, watch } from 'vue';
import _ from 'lodash';
import { useAuth } from '@/stores/auth';
import IDate from '@/components/forms/inputs/iDate.vue';
import { storeToRefs } from 'pinia';
import AppForm from '@/components/core/AppForm.vue';
import Time from '@/objects/Time';
import Formatter from '@/objects/Formatter';

const emits = defineEmits(['update:modelValue']);
defineProps({
    step: {
        type: Number,
        default: 0
    },
});
const { user } = storeToRefs(useAuth());
const { form, validate, reset, setForm } = useFormValidator({}, rules, [
    'months',
    'products',
    'product_ids',
]);
const params = computed(() => ({
    created_by: [user.value?.id],
    promoted: false
}));

const onProducts = (products) => {
    products = products || [];

    form.products = products;
    form.product_ids = products.map(product => product.id);
}

const onDate = (value) => {
    if (! value || value.length !== 2) return 0;

    const sortedDates = _.sortBy(value);

    form.months = Time.differenceInMonths(sortedDates[0], sortedDates[1]) + 1;
}

watch(form, () => emits('update:modelValue', form));

defineExpose({ validate, reset, setForm });
</script>
