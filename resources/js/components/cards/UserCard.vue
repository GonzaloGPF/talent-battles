<template>
  <v-card :class="{'promoted': user?.last_donation}" class="overflow-visible">
    <div class="d-flex align-center">
      <div class="relative">
        <app-avatar
          :model="user"
          :color="user?.color"
          :size="smAndDown ? 80 : 120"
          navigate
          class="md:-mt-8 -mt-8 -ml-3"
        />
        <circular-progress
          :model-value="user?.rating"
          class="bg-gray-900 absolute bottom-0 right-0 rounded-full"
        />
      </div>
      <div class="w-full">
        <v-card-title class="d-flex justify-space-between align-center truncate">
          <app-button
            v-if="user?.id"
            :label="user?.nick_name"
            :route="{ name: 'users.show', params: { id: user?.id } }"
            size="small"
            variant="text"
            class="d-block brand-font"
          />
          <app-field
            v-if="user?.verified_at"
            :title="Translator.tl('verified')"
            :success="true"
            :inline="true"
            class="text-green"
            icon="mdi-check"
          />
        </v-card-title>
        <v-card-text style="min-height: 80px">
          <app-field
            :title="Translator.ta('categories')"
            :model-value="user?.categories"
            constant="category"
            class="mb-2"
          />
          <blockquote
            v-if="user?.quote"
            class="font-italic"
            v-text="user?.quote"
          />
        </v-card-text>
      </div>
      <!--        <v-card-subtitle v-text="user.experience" />-->
    </div>

    <v-card-actions>
      <ReactionButtons
        v-if="actions?.length"
        :model="user"
        :actions="actions"
      />
    </v-card-actions>
  </v-card>
</template>
<script setup>
import CircularProgress from '@/components/shared/CircularProgress.vue';
import Translator from '@/objects/Translator';
import {useDisplay} from 'vuetify';
import {defineAsyncComponent} from 'vue'

defineProps({
  user: {
    type: Object,
    required: true,
    default: null
  },
  actions: {
    type: Array,
    default: () => []
  }
});
const AppButton = defineAsyncComponent(() => import('@/components/core/AppButton.vue'));
const AppField = defineAsyncComponent(() => import('@/components/core/AppField.vue'));
const ReactionButtons = defineAsyncComponent(() => import('@/components/buttons/ReactionButtons.vue'));
const AppAvatar = defineAsyncComponent(() => import('@/components/core/AppAvatar.vue'));
const { smAndDown } = useDisplay();
</script>
<style>
.base-card.contributor {
  outline: solid antiquewhite;
}
</style>
