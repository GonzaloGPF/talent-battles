<template>
  <div>
    <div class="text-center mb-3">
      <app-avatar
        type="user"
        :size="100"
        :model="user"
        :editable="me?.id === user?.id"
        @success="read(me?.id)"
      />
    </div>

    <v-sheet
      elevation="2"
      class="p-3 rounded-lg mb-5"
      color="grey-darken-3"
    >
      <user-info :model-value="user" />
    </v-sheet>

    <profile-editor />

    <password-editor class="mt-3" />
  </div>
</template>
<script setup>
import ProfileEditor from '@/components/editors/ProfileEditor.vue';
import UserInfo from '@/components/info/UserInfo.vue';
import { storeToRefs } from 'pinia';
import AppAvatar from '@/components/core/AppAvatar.vue';
import { useAuth } from '@/stores/auth';
import { useUser } from '@/stores/users';
import PasswordEditor from '@/components/editors/PasswordEditor.vue';

const { read } = useUser();
const { user } = storeToRefs(useUser());
const { user: me } = storeToRefs(useAuth());
</script>
