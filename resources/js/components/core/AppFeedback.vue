<template>
  <div>
    <AppProgress
      v-show="loading"
      :loading="loading"
      indeterminate
    />

    <NotVerifyAlert
      v-if="isLogged && !user?.verified_at"
      class="m-2 max-w-max"
    />

    <AppSnackBar v-if="isShowing" />

    <AppFlashMessages v-if="hasMessages" />

    <AppConfirm v-if="showConfirm" />
  </div>
</template>
<script setup>
import {defineAsyncComponent} from 'vue'
import {storeToRefs} from 'pinia'
import {useSnackbar} from '@/stores/snackbar'
import {useLoader} from '@/stores/loader'
import {useConfirm} from '@/stores/confirm'
import {useFlashMessages} from '@/stores/flashMessages'
import {useAuth} from '@/stores/auth'

const AppProgress = defineAsyncComponent(() => import('@/components/core/AppProgress.vue'));
const NotVerifyAlert = defineAsyncComponent(() => import('@/components/alerts/NotVerifiedAlert.vue'));
const AppSnackBar = defineAsyncComponent(() => import('@/components/core/AppSnackbar.vue'));
const AppFlashMessages = defineAsyncComponent(() => import('@/components/core/AppFlashMessages.vue'));
const AppConfirm = defineAsyncComponent(() => import('@/components/core/AppConfirm.vue'));

const { user, isLogged } = storeToRefs(useAuth());
const { isShowing } = storeToRefs(useSnackbar());
const { loading } = storeToRefs(useLoader());
const { show: showConfirm } = storeToRefs(useConfirm());
const { hasMessages } = storeToRefs(useFlashMessages());
</script>
