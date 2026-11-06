<template>
  <div class="d-flex flex-column fill-height">
    <app-buttons
      :buttons="socialButtons"
      class="ml-auto mb-6 -mt-3 border rounded"
      @click="Utils.openTab(props.modelValue?.social_settings?.[$event])"
    />

    <div class="grid grid-cols-3 gap-y-2 text-center items-center w-full">
      <app-field
        :title="Translator.modelTitle('battle', true)"
        :model-value="modelValue?.battles?.length || 0"
        icon="mdi-sword-cross"
      />

      <circular-progress
        :model-value="modelValue?.rating || 0"
        class="caption w-100"
      />

      <app-field
        :title="Translator.ta('experience')"
        :model-value="modelValue?.experience + 'xp' || 0"
        :tooltip="Translator.tConstName('level', modelValue?.level?.name)"
        icon="$progress_star"
      />

      <app-field
        :title="Translator.tl('beats')"
        :model-value="modelValue?.beats_count || 0"
        icon="$beat"
      />

      <app-field
        :title="Translator.tl('samples')"
        :model-value="modelValue?.samples_count || 0"
        icon="$sample"
      />

      <app-field
        :title="Translator.tl('songs')"
        :model-value="modelValue?.songs_count || 0"
        icon="$song"
      />

      <app-field
        :title="Translator.tl('designs')"
        :model-value="modelValue?.designs_count || 0"
        icon="$design"
      />

      <app-field
        :title="Translator.modelTitle('album', true)"
        :model-value="modelValue?.albums_count || 0"
        icon="$album"
      />

      <!--
            <app-field
                :title="tl('points')"
                :model-value="modelValue.points"
                icon="mdi-star"
            />
            -->
    </div>
    <!--
        <div class="d-flex items-center mt-2">
            <app-field
                v-if="isInternal || itsMe"
                :model-value="modelValue.email"
                class="mr-3"
            />
            <app-field
                :title="isVerified ? tl('verified') : tl('not_verified')"
                :success="isVerified"
                :error="!isVerified"
                :icon="isVerified ? 'mdi-check' : 'mdi-close'"
                :inline="true"
                :model-value="(isInternal || itsMe) ? null : tl('verified')"
            />
            <app-field
                :model-value="modelValue.created_at"
                :inline="true"
                type="date"
                class="ml-auto -mb-5"
            />
        </div>
        <blockquote
            v-if="modelValue.quote"
            class="mt-2 flex-grow-1 truncate max-h-6"
            v-text="user.quote"
        />
        -->
  </div>
</template>
<script setup>
import Translator from '@/objects/Translator';
import CircularProgress from '@/components/shared/CircularProgress.vue';
import { computed } from 'vue';
import Utils from '@/objects/Utils';
import AppButtons from '@/components/core/AppButtons.vue';
import AppField from '@/components/core/AppField.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const socialButtons = computed(() => {
    const buttons = [];

  if (props.modelValue?.social_settings?.web) {
    buttons.push('web');
  }

    if (props.modelValue?.social_settings?.youtube) {
        buttons.push('youtube');
    }

    if (props.modelValue?.social_settings?.twitter) {
        buttons.push('twitter');
    }

    if (props.modelValue?.social_settings?.soundcloud) {
        buttons.push('soundcloud');
    }

    if (props.modelValue?.social_settings?.facebook) {
        buttons.push('facebook');
    }

    if (props.modelValue?.social_settings?.instagram) {
        buttons.push('instagram');
    }

    if (props.modelValue?.social_settings?.soundclick) {
        buttons.push('soundclick');
    }

    return buttons;
});
</script>
