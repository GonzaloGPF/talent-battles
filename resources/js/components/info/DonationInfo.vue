<template>
  <div>
    <app-field
      v-if="modelValue?.createdBy?.nick_name"
      :model-value="modelValue?.createdBy?.nick_name"
      :route-data="{name: 'users.show', params: {id: modelValue?.created_by}}"
      :inline="true"
      type="route"
      class="brand-font"
    />
    <span
      v-else
      class="brand-font"
      v-text="Translator.tl('anonymous')"
    />
    <div class="d-flex flex-column mt-3">
      <app-field
        :label="Translator.actionTitle('donated')"
        :model-value="modelValue?.amount"
        type="money"
        inline
      />
      <app-date
        :model-value="modelValue?.created_at"
        :humanize="true"
        class="text-caption"
      />
    </div>
    <app-quote
      v-if="modelValue?.message"
      :text="modelValue?.message"
      class="mt-2 font-italic"
    />
  </div>
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppField from '@/components/core/AppField.vue';
import AppDate from '@/components/core/AppDate.vue';
import AppQuote from '@/components/core/AppQuote.vue';

defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
</script>
