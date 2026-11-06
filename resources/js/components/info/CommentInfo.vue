<template>
  <div>
    <small class="flex items-center">
      <app-field
        :model-value="modelValue?.createdBy?.nick_name"
        :route-data="{name: 'users.show', params: {id: modelValue?.createdBy?.id}}"
        :inline="true"
        type="route"
        class="brand-font"
      />
      <app-date
        :model-value="modelValue?.created_at"
        :humanize="true"
        class="text-caption text-grey"
      />
    </small>

    <app-text
      :model-value="modelValue?.content"
      class="pl-2 comment-content"
    />
  </div>
</template>
<script setup>
import AppField from '@/components/core/AppField.vue';
import AppDate from '@/components/core/AppDate.vue';
import AppText from '@/components/core/AppText.vue';

defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
</script>
<style>
.comment-content {
    /*border-radius: 5px;*/
    border-left: 1px solid rgba(0, 0, 0, 0.1);
    max-width: 500px;
}

.comment-content > p {
    margin: 0;
}
</style>
