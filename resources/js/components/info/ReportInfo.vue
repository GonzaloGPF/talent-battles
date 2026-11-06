<template>
  <div>
    <app-field
      v-if="isAdmin"
      :label="Translator.ta('created_by')"
      :model-value="modelValue?.createdBy?.name"
      :route-data="{name: 'users.show', params: {id: modelValue?.created_by}}"
      type="route"
    />

    <app-field
      :label="Translator.tl('type')"
      :model-value="Translator.modelTitle(modelValue?.reportable_type)"
      inline
      class="d-block"
    />
    <app-field
      :label="Translator.tl('id')"
      :model-value="modelValue?.reportable_id"
      inline
      class="d-block"
    />

    <app-field
      :label="Translator.ta('message')"
      :model-value="modelValue?.message"
    />

    <app-attachments
      v-if="modelValue?.file"
      :files="[modelValue?.file]"
      :downloable="true"
    />

    <v-row v-if="modelValue?.resolution">
      <v-col :cols="12" :md="6" :lg="3">
        <app-field
          :label="Translator.ta('resolution')"
          :model-value="modelValue?.resolution"
        />
      </v-col>
      <v-col :cols="12" :md="6" :lg="3">
        <app-field
          :label="Translator.ta('closed_at')"
          :model-value="modelValue?.closed_at"
          type="datetime"
        />
      </v-col>
    </v-row>
  </div>
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppField from '@/components/core/AppField.vue';
import AppAttachments from '@/components/core/AppAttachments.vue';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';

defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});

const { isAdmin } = storeToRefs(useAuth());
</script>
