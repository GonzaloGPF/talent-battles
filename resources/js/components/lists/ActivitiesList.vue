<template>
  <v-list
    v-if="activities.length"
    :items="items"
    density="compact"
    lines="two"
    @click:select="onClick"
  >
    <!-- TODO: remove this subtitle template -->
    <template #subtitle=" { item }">
      {{ item.subtitle }}
    </template>
  </v-list>
  <app-empty
    v-else
    model="activity"
  />
</template>
<script setup>
import { computed } from 'vue';
import Translator from '@/objects/Translator';
import CreationHelper from '@/helpers/CreationHelper';
import AppEmpty from '@/components/core/AppEmpty.vue';
import { useRouter } from 'vue-router';
import Formatter from '@/objects/Formatter';

const props = defineProps({
    activities: {
        type: Array,
        default: () => []
    }
});

const { push } = useRouter();

const activitiesMap = {
    new_creation: 'uploaded',
    do_comment: 'commented',
    do_like: 'liked',
    do_favorite: 'did_favorite',
    do_download: 'downloaded',
    win_battle: 'won_a_battle',
};

const items = computed(() => props.activities.map(activity => ({
    title: Translator.tl(activitiesMap[activity.activity_type]) + ' ' + (activity.target.nick || activity.target.name),
    value: activity.id,
    subtitle: Formatter.date(activity.created_at, true),
    route: getRoute(activity)
    // props: {
    //     prependIcon: `activity_type_${activity.activity_type}`,
    // }
})));

function onClick(event) {
    const item = items.value?.find(item => item.value === event.id);
    push(item.route);
}

function getRoute(activity) {
  if (activity.target.model === 'user') {
      return {
        name: 'users.show',
        params: { id: activity.target.id }
      }
  }

  return CreationHelper.getCreationShowRoute(activity.target);
}
</script>
