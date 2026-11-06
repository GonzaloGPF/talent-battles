<template>
  <div>
    <data-table
      ref="tableElement"
      :headers="headers"
      :filter-fields="filters"
      :title="title"
      :params="params"
      url="invitations"
      @create="onCreate"
    />
    <app-modal
      :model-value="showModal"
      :title="modalTitle"
      :loading="loading"
      @ok="onSubmit"
      @close="onClose"
    >
      <app-alert
        :message="Translator.t('help.add_friends_to_invite')"
        border="start"
        variant="tonal"
        class="mb-6"
      />
      <field-block :title="Translator.tl('invite_a_friend')" class="px-2 pb-5">
        <invitation-form
          ref="formElement"
          class="mt-3"
          @keyup.enter="addInvitation"
        />
        <div class="mx-auto w-100 text-center">
          <app-button
            :loading="loading"
            :label="Translator.actionTitle('add')"
            color="blue"
            @click="addInvitation"
          />
        </div>
      </field-block>
      <simple-table
        v-if="invitations.length"
        :items="invitations"
        :headers="headers.filter(header => ['name', 'email'].includes(header.key))"
        :actions="['delete']"
        @delete="removeInvitation"
      />
    </app-modal>
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/invitationHeaders';
import filters from '@/components/filters/invitationFilters';
import DataTable from '@/components/tables/DataTable.vue';
import AppModal from '@/components/core/AppModal.vue';
import InvitationForm from '@/components/forms/InvitationForm.vue';
import { nextTick, ref } from 'vue';
import useAdminCrud from '@/composables/useAdminCrud';
import FieldBlock from '@/components/core/FieldBlock.vue';
import AppButton from '@/components/core/AppButton.vue';
import Translator from '@/objects/Translator';
import useHttp from '@/composables/useHttp';
import SimpleTable from '@/components/tables/SimpleTable.vue';
import { useConfirm } from '@/stores/confirm';
import AppAlert from '@/components/core/AppAlert.vue';

defineProps({
    params: {
        type: Object,
        default: null
    }
});
const formElement = ref(null);
const tableElement = ref(null);
const { showConfirm } = useConfirm();
const {
  title,
  modalTitle,
  showModal,
  // loading,
  onCreate,
  onSuccess,
  onClose,
} = useAdminCrud('invitation', tableElement, formElement);
const { postJson, loading } = useHttp();

const invitations = ref([]);

const addInvitation = () => {
  if (loading.value) return;

  const data = formElement.value?.validate();

  if (! data) {
    return null;
  }

  invitations.value.push({ ...data });
  nextTick(() => formElement.value?.reset());
};

const onSubmit = async () => {
  const accepted = await showConfirm(Translator.t('help.you_will_send_invitations', { total: invitations.value?.length}))

  if (! accepted) {
    return;
  }

  postJson('invitations', { invitations: invitations.value})
    .then(onSuccess);
}

const removeInvitation = (invitation) => {
  const index = invitations.value.indexOf(invitation);

  invitations.value.splice(index, 1);
}
</script>
