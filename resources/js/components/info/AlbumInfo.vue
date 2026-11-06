<template>
  <v-row>
    <v-col :cols="12" :md="6">
      <app-image
        v-if="withImage && modelValue?.image"
        :file="modelValue?.image"
        :contain="true"
        class="!max-w-lg"
      />
    </v-col>
    <v-col :cols="12" :md="withImage ? 6 : 12">
      <div class="d-flex flex-column fill-height justify-center">
        <!--
            <app-button
                v-if="_.get(modelValue, 'created_by')"
                size="small"
                :title="_.get(modelValue, 'createdBy.name')"
                :route="{name: 'users.show', params: {id: _.get(modelValueObj, 'created_by')}}"
                color="grey lighten-1"
                block
            />
            -->
        <div class="grid grid-cols-4 gap-y-2 text-center items-center w-full">
          <app-field
            v-if="modelValue?.beats_count"
            :title="Translator.tl('beats')"
            :model-value="modelValue?.beats_count"
            icon="$beat"
          />

          <app-field
            v-if="modelValue?.samples_count"
            :title="Translator.tl('samples')"
            :model-value="modelValue?.samples_count"
            icon="$sample"
          />

          <app-field
            v-if="modelValue?.songs_count"
            :title="Translator.tl('songs')"
            :model-value="modelValue?.songs_count"
            icon="$song"
          />

          <app-field
            v-if="modelValue?.designs_count"
            :title="Translator.tl('designs')"
            :model-value="modelValue?.designs_count"
            icon="$design"
          />
        </div>
        <app-field
          :title="Translator.ta('name')"
          :model-value="modelValue?.name"
        />
        <app-field
          :title="Translator.ta('released')"
          :model-value="modelValue?.released"
          type="date"
        />
        <app-field
          v-if="modelValue?.description"
          :label="Translator.ta('description')"
          :model-value="modelValue?.description"
          class="mt-4"
        />
      </div>
    </v-col>
  </v-row>
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppField from '@/components/core/AppField.vue';
import AppImage from '@/components/core/AppImage.vue';

defineProps({
  modelValue: {
    type: Object,
    default: null
  },
  withImage: {
    type: Boolean,
    default: false
  }
});
</script>
