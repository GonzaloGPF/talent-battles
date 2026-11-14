<template>
  <clean-layout
    :loading="loading"
    :title="Translator.tl('claim_profile')"
  >
    <div class="md:max-w-md">
      <app-alert
        :message="Translator.t('help.claim_profile_intro', { name })"
        variant="tonal"
        class="my-4 md:my-8"
      />

      <h2
        class="text-h6 mb-2"
        v-text="Translator.tl('keep_profile')"
      />
      <p
        class="text-body-2 mb-4"
        v-text="Translator.t('help.keep_profile')"
      />
      <profile-claim-form
        ref="formElement"
        @keyup.enter="keep"
      />
      <app-button
        :label="Translator.tl('keep_profile')"
        :loading="loading"
        size="large"
        color="info"
        class="brand-font mt-6 w-100"
        @click="keep"
      />

      <v-divider class="my-8" />

      <h2
        class="text-h6 mb-2"
        v-text="Translator.tl('delete_profile')"
      />
      <p
        class="text-body-2 mb-4"
        v-text="Translator.t('help.delete_profile')"
      />
      <app-button
        :label="Translator.tl('delete_profile')"
        :loading="loading"
        variant="outlined"
        color="error"
        class="w-100"
        @click="decline"
      />
    </div>
  </clean-layout>
</template>
<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppAlert from '@/components/core/AppAlert.vue';
import AppButton from '@/components/core/AppButton.vue';
import ProfileClaimForm from '@/components/forms/ProfileClaimForm.vue';
import CleanLayout from '@/layouts/CleanLayout.vue';
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';
import { useConfirm } from '@/stores/confirm';

const { loading, postJson, deleteJson } = useHttp();
const { setUser } = useAuth();
const { showConfirm } = useConfirm();
const { push } = useRouter();

const formElement = ref();
const profileClaim = window.profile_claim;
const name = computed(() => profileClaim?.user?.nick_name ?? '');

const keep = () => {
  const data = formElement.value?.validate();

  if (! data) return;

  postJson(profileClaim.claim_url, data)
    .then(({ data: user }) => {
      setUser(user);
      push({ name: 'profile.show' });
    });
}

const decline = async () => {
  const accept = await showConfirm(Translator.t('help.confirm_delete_profile', { name: name.value }));

  if (! accept) return;

  deleteJson(profileClaim.decline_url)
    .then(() => push({ name: 'welcome' }));
}
</script>
