<template>
  <div>
    <!--
        <app-field
            :model-value="_.get(modelValue, 'createdBy.nick_name')"
            :route-modelValue="{name: 'users.show', params: {id: _.get(modelValue, 'createdBy.id')}}"
            :inline="true"
            type="route"
            class="font-weight-bold"
        />
        -->
    <span v-text="Translator.tl(activitiesMap[modelValue?.activityType?.name])" />

    <app-field
      v-if="modelValue?.target"
      :model-value="modelValue?.target?.name"
      :route-data="CreationHelper.getCreationShowRoute(modelValue?.target)"
      :inline="true"
      type="route"
      class="font-weight-bold"
    />

    <app-date
      :model-value="modelValue.created_at"
      :humanize="true"
      class="font-weight-light"
    />
  </div>
</template>
<script setup>
import Translator from '@/objects/Translator';
import CreationHelper from '@/helpers/CreationHelper';
import AppField from '@/components/core/AppField.vue';
import AppDate from '@/components/core/AppDate.vue';

defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const activitiesMap = {
    new_creation: 'uploaded',
    do_comment: 'commented',
    do_like: 'liked',
    do_favorite: 'did_favorite',
    do_download: 'downloaded',
    win_battle: 'won_a_battle',
}
</script>
