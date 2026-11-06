<template>
  <div>
    <v-row>
      <v-col :cols="12" :md="3">
        <app-field
          :label="Translator.ta('product_type')"
          :model-value="Translator.tConstName('product_types', modelValue?.product_type)"
          :inline="true"
        />
      </v-col>
      <v-col :cols="12" :md="6">
        <app-field
          :label="Translator.ta('description')"
          :model-value="modelValue?.description"
          :inline="true"
        />
      </v-col>
      <v-col v-if="! isMine" :cols="12" :md="3">
        <app-field
          :model-value="modelValue?.createdBy?.name"
          :route-data="{name: 'users.show', params: {id: modelValue?.created_by}}"
          :inline="true"
          type="route"
        />
      </v-col>
    </v-row>

    <v-row>
      <v-col :cols="12" :md="6" :lg="3">
        <app-field
          :label="Translator.ta('price')"
          :model-value="modelValue?.price"
          :inline="true"
          type="money"
        />
      </v-col>
      <v-col :cols="12" :md="6" :lg="3">
        <app-field
          :label="Translator.ta('quantity')"
          :model-value="modelValue?.quantity"
          :inline="true"
        />
      </v-col>
      <v-col :cols="12" :md="6" :lg="3">
        <app-field
          :label="Translator.ta('total')"
          :model-value="modelValue?.total"
          :inline="true"
          type="money"
        />
      </v-col>
      <v-col
        v-if="modelValue?.file || hasCreation"
        :cols="12"
        :md="6"
        :lg="3"
      >
        <div class="flex space-x-2">
          <app-download
            v-if="modelValue?.file"
            :file="modelValue?.file"
            with-text
            size="small"
          />
          <app-button
            v-if="hasCreation"
            :title="Translator.actionTitle('view')"
            :loading="loading"
            icon="mdi-eye"
            size="small"
            variant="text"
            @click="onCreation"
          />
        </div>
      </v-col>
    </v-row>
  </div>
</template>
<script setup>
import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';
import CreationHelper from '@/helpers/CreationHelper';
import { computed } from 'vue';
import useHttp from '@/composables/useHttp';
import AppField from '@/components/core/AppField.vue';
import AppDownload from '@/components/core/AppDownload.vue';
import { storeToRefs } from 'pinia';
import Formatter from '@/objects/Formatter';
import AppButton from '@/components/core/AppButton.vue';
import { useRouter } from 'vue-router';

const { getJson, loading } = useHttp();
const { push } = useRouter();
const { user } = storeToRefs(useAuth());
const props = defineProps({
    modelValue: {
        type: Object,
        default: () => null
    }
});
const hasCreation = computed(() => ['track', 'design'].includes(props?.modelValue?.orderable?.model));
const isMine = computed(() => props.modelValue?.created_by === user.value?.id);
const productId = computed(() => props.modelValue?.orderable?.id);

function onCreation() {
    if (! hasCreation.value) return;

    const model = props.modelValue?.orderable?.model;
    const url = `${Formatter.plural(model)}/${productId.value}`;

    const { data } = getJson(url);

    push(CreationHelper.getCreationShowRoute(data));
}
</script>
