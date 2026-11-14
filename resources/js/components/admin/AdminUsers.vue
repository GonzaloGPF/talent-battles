<template>
  <div>
    <data-table
      ref="tableElement"
      :headers="headers"
      :filter-fields="filters"
      :title="title"
      :actions="actionsFor"
      :params="params"
      :searchable="true"
      :events-reload="[EventTypes.backend['.UserUpdated']]"
      url="users"
      @view="onView"
      @create="onCreate"
      @edit="onEdit"
      @delete="onDelete"
      @claim_link="onClaimLink"
      @revoke_claim_link="onRevokeClaimLink"
    >
      <template #default="{ item }">
        <user-details :model-id="item.id" />
      </template>
    </data-table>

    <app-modal
      :model-value="showModal"
      :title="modalTitle"
      :loading="loading"
      @ok="onSubmit"
      @close="onClose"
    >
      <user-form ref="formElement" />
    </app-modal>
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/userHeaders';
import filters from '@/components/filters/userFilters';
import UserForm from '@/components/forms/UserForm.vue';
import UserDetails from '@/components/tables/details/UserDetails.vue';
import useAdminCrud from '@/composables/useAdminCrud';
import { ref } from 'vue';
import AppModal from '@/components/core/AppModal.vue';
import DataTable from '@/components/tables/DataTable.vue';
import EventTypes from '@/events/EventTypes';
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';
import { useFlashMessages } from '@/stores/flashMessages';
import { useConfirm } from '@/stores/confirm';

defineProps({
    params: {
        type: Object,
        default: null
    }
});
const formElement = ref(null);
const tableElement = ref(null);
const {
    title,
    modalTitle,
    showModal,
    loading,
    onCreate,
    onEdit,
    onDelete,
    onSubmit,
    onClose,
    onView,
} = useAdminCrud('user', tableElement, formElement);
const { postJson, deleteJson } = useHttp();
const { pushFlashMessage } = useFlashMessages();
const { showConfirm } = useConfirm();

/**
 * Profiles of users can be handed to their owners with a link, which can be revoked while it works; staff accounts
 * cannot be handed over.
 */
const actionsFor = (user) => {
  if (user.role !== 'user') {
    return ['view', 'edit', 'delete'];
  }

  return ['view', 'edit', 'claim_link', ...(user.claim_link_expires_at ? ['revoke_claim_link'] : []), 'delete'];
};

/**
 * Copies the link to send to the owner of the profile. Without a clipboard (outside https) it shows it to copy by hand.
 */
const onClaimLink = (user) => postJson(`users/${ user.id }/claim_link`)
    .then(async ({ data }) => {
      tableElement.value?.refresh();

      try {
        await navigator.clipboard.writeText(data.url);
        pushFlashMessage(Translator.t('help.claim_link_copied', { date: Formatter.date(data.expires_at) }), { type: 'success' });
      } catch {
        window.prompt(Translator.t('actions.claim_link'), data.url);
      }
    });

const onRevokeClaimLink = async (user) => {
  const accept = await showConfirm(Translator.t('help.confirm_revoke_claim_link', { name: user.nick_name }));

  if (! accept) return;

  deleteJson(`users/${ user.id }/claim_link`)
    .then(() => tableElement.value?.refresh());
};
</script>
