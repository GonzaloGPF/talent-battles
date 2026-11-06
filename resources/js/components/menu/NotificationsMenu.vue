<template>
  <app-button
    :loading="loading || httpLoading"
    :label="Translator.modelTitle('notification', true)"
    :disabled="! items.length"
    variant="text"
    size="small"
  >
    <template #menu>
      <v-menu
        v-model="showMenu"
        activator="parent"
        offset-y
      >
        <notifications-list
          v-if="items.length"
          :notifications="items"
          :view-more="! isLastPage && items.length > 15"
          :disabled="loading || httpLoading"
          @read-all="onReadAll"
          @open="onOpen"
          @delete="readNotification"
          @view-more="filterForm.page++"
        />
      </v-menu>
    </template>
  </app-button>
</template>
<script setup>
import EventBus from '@/events/EventBus';
import eventTypes from '@/events/EventTypes';
import useHttp from '@/composables/useHttp';
import { computed, reactive, ref, watch } from 'vue';
import Translator from '@/objects/Translator';
import Utils from '@/objects/Utils';
import AppButton from '@/components/core/AppButton.vue';
import { useConfirm } from '@/stores/confirm';
import { useFlashMessages } from '@/stores/flashMessages';
import NotificationsList from '@/components/lists/NotificationsList.vue';
import Notification from '@/objects/Notification';
import useFetchItems from '@/composables/useFetchItems';

const emits = defineEmits(['items']);
const { loading: httpLoading, getJson, putJson } = useHttp();
const { pushFlashMessage } = useFlashMessages();
const { showConfirm } = useConfirm();
const showMenu = ref(false);
const filterForm = reactive({ page: 1 });
const { items, currentPage, totalPages, loading, total } = useFetchItems('notifications', {
    is_read: false,
    sort: 'created_at|desc',
}, filterForm); // !isLogged.value
const isLastPage = computed(() => currentPage.value === totalPages.value);

const onOpen = (notification) => {
    const url = notification.url;

    if (url) {
        Utils.openTab(url);
    }

    readNotification(notification);
}

const removeNotification = (notification) => {
    items.value = items.value.filter(item => item.id !== notification.id);

    if (total.value > 0) {
        total.value--;
    }
}

const readNotification = (notification) => {
    return putJson(`notifications/read/${ notification.id }`)
        .then(() => removeNotification(notification));
}

const cleanNotifications = () => {
    items.value = [];
    total.value = 0;
    filterForm.page = 1;
}

const onReadAll = async () => {
    const accept = await showConfirm(Translator.t('help.mark_all_notifications_as_read'));

    if (! accept) return;

    putJson('notifications/read')
        .then(cleanNotifications);
}

const onNewNotification = (notification) => {
    if (! notification) return;

    fetchNotification(notification.noty_id)
}

const fetchNotification = (notyId) => {
    getJson('notifications', { params: { noty_id: notyId } })
        .then(addNotification);
}

const addNotification = ({ data }) => {
    const notification = data[0];
    const notificationIds = (items.value || []).map(notification => notification.id);

    if (! notification || notificationIds.includes(notification.id)) return;

    items.value.unshift(notification);
    total.value++;

    pushFlashMessage(Notification.toFlashMessage(notification));
}

const toggleMenu = () => {
  showMenu.value = !showMenu.value;
}

EventBus.on(eventTypes.NEW_NOTIFICATION, onNewNotification);

watch(items, () => emits('items', items.value));

defineExpose({
  toggleMenu,
});
</script>
