<template>
  <div>
    <v-row>
      <v-col :cols="12" :lg="3">
        <app-field
          :label="Translator.ta('created_at')"
          :model-value="modelValue?.created_at"
          type="date"
        />
      </v-col>
      <v-col :cols="12" :lg="3">
        <app-field
          :label="Translator.ta('succeeded')"
          :model-value="modelValue?.succeeded"
          type="date"
        />
      </v-col>
      <v-col :cols="12" :lg="3">
        <app-field
          :label="Translator.modelTitle('orderLine', true)"
          :model-value="modelValue?.order_lines_count"
        />
      </v-col>
    </v-row>

    <simple-table
      :items="modelValue?.orderLines"
      :headers="productHeaders"
      :actions="getActions"
      class="my-5"
      @view="push(CreationHelper.getCreationShowRoute($event.orderable))"
      @download="downloadFile($event.file)"
    />

    <div class="flex justify-end text-end">
      <field-block
        :title="Translator.tl('summary')"
        class="max-w-md"
      >
        <div class="p-5">
          <app-field
            :label="Translator.ta('brute')"
            :model-value="modelValue?.brute"
            :inline="true"
            type="money"
            class="mt-1 d-block"
          />
          <app-field
            :label="Translator.ta('discount_amount')"
            :model-value="modelValue?.discount_amount"
            :inline="true"
            type="money"
            class="mt-1 d-block"
          />
          <app-field
            :label="Translator.ta('subtotal')"
            :model-value="modelValue?.subtotal"
            :inline="true"
            type="money"
            class="mt-1 d-block"
          />
          <app-field
            :label="Translator.ta('tax_amount')"
            :model-value="modelValue?.tax_amount"
            :inline="true"
            type="money"
            class="mt-1 d-block"
          />
          <app-field
            :label="Translator.ta('total')"
            :model-value="modelValue?.total"
            :inline="true"
            type="money"
            class="mt-1 d-block"
          />
        </div>
      </field-block>
    </div>
  </div>
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppField from '@/components/core/AppField.vue';
import FieldBlock from '@/components/core/FieldBlock.vue';
import SimpleTable from '@/components/tables/SimpleTable.vue';
import productHeaders from '@/components/tables/headers/cartItemHeaders';
import CreationHelper from '@/helpers/CreationHelper';
import { useRouter } from 'vue-router';
import useHttp from '@/composables/useHttp';

defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const { push } = useRouter()
const { downloadFile } = useHttp()
function getActions(item) {
    if (['track', 'design'].includes(item.orderable?.model)) {
        return ['view', 'download'];
    }
}
</script>
