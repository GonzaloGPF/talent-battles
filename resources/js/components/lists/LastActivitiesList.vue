<template>
  <v-card :title="Translator.tl('last_activity_from', { name: user.nick_name })">
    <template #progress>
      <v-progress-linear
        :active="loading"
        indeterminate
      />
    </template>
    <v-card-text>
      <activities-list :activities="lastActivities" />
    </v-card-text>
  </v-card>
</template>
<script setup>
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import { computed, ref, toRef, watch } from 'vue';
import ActivitiesList from '@/components/lists/ActivitiesList.vue';

const props = defineProps({
  user: {
    type: Object,
    default: null
  },
});
const { loading, getJson } = useHttp();
// const { user } = storeToRefs(useAuth());
const userProps = toRef(props, 'user');
const lastActivities = ref([]);
const params = computed(() => ({
  created_by: userProps.value.id,
  not_receiver_id: userProps.value.id,
  target_type: ['track', 'design', 'user'],
  with: ['target', 'createdBy'],
  limit: 5,
}));

const makeRequest = async () => {
  if (! params.value?.created_by) return;

  const { data } = await getJson('activities', { params: params.value });

  lastActivities.value = data
}

watch(userProps, makeRequest, { immediate: true });

</script>
