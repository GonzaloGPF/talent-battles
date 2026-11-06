<template>
  <v-list
    :items="items"
    :disabled="disabled"
    density="compact"
    lines="two"
    class="max-w-lg"
    @update:selected="selected = $event"
  >
    <!-- TODO: remove this subtitle template -->
    <template #subtitle=" { item }">
      {{ item.subtitle }}
    </template>
    <template #append="{ item }">
      <app-button
        v-if="!['read_all', 'view_more'].includes(item.value)"
        :title="Translator.actionTitle('read')"
        variant="text"
        size="small"
        icon="mdi-delete"
        @click.prevent="$emit('delete', findById(item.value))"
      />
    </template>
  </v-list>
</template>
<script setup>
import { computed, ref, watch } from 'vue';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import Notification from '@/objects/Notification';

const emits = defineEmits([
    'readAll',
    'viewMore',
    'open',
    'delete',
]);
const props = defineProps({
    notifications: {
        type: Array,
        default: () => []
    },
    viewMore: {
        type: Boolean
    },
    disabled: {
        type: Boolean
    }
});
const selected = ref(null);

const items = computed(() => {
    const allItems = [{
        type: 'header',
        title: Translator.actionTitle('read_all'),
        value: 'read_all',
        props: {
            class: 'text-center'
        }
    }];

    props.notifications
        .map(toItem)
        .forEach(item => allItems.push(item));

    if (props.viewMore) {
        allItems.push({
            title: Translator.actionTitle('view_more'),
            value: 'view_more',
            props: {
                class: 'text-center'
            }
        });
    }

    return allItems;
});

const toItem = notification => ({
    title: Notification.getTitle(notification),
    subtitle: Notification.getMessage(notification),
    value: notification.id,
    props: {
        prependIcon: notification.icon,
    },
});

const findById = id => props.notifications.find(notification => notification.id === id);

watch(selected, (value) => {
    value = value[0];

    if (value === 'read_all') {
        return emits('readAll');
    }

    if (value === 'view_more') {
        return emits('viewMore');
    }

    emits('open', findById(value));
})
</script>
