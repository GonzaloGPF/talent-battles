<template>
  <v-card
    :loading="loading"
    class="summary w-full rounded-lg overflow-visible"
  >
    <v-card-text>
      <div class="d-flex flex-col md:flex-row align-center">
        <div
          class="d-flex flex-column align-center summary-icon text-white rounded fill-height -mt-16 elevation-2 py-1 px-5 mr-5"
          :class="`bg-${color}`"
        >
          <app-icon
            :icon="icon"
            size="x-large"
          />
          <span
            class="text-h6 mt-1"
            v-text="title"
          />

          <span
            class="text-subtitle-1 -mt-1"
            v-text="subTitle"
          />
        </div>

        <ul
          :class="{'gray': selected}"
          class="d-flex flex-grow-1 flex-column list-reset summary-text align-center justify-center p-1 w-100"
        >
          <li
            v-for="(label, index) in labels"
            :key="index"
            class="d-flex justify-space-between w-full"
          >
            <span
              class="inline-block"
              v-text="label.text"
            />
            <span
              class="inline-block"
              v-text="label.value"
            />
          </li>
        </ul>
      </div>
    </v-card-text>
  </v-card>
</template>
<script setup>

import { computed, ref } from "vue";
import AppIcon from '@/components/core/AppIcon.vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
        default: null
    },
    subTitle: {
        type: [String, Number],
        default: null
    },
    icon: {
        type: String,
        required: true,
        default: null
    },
    color: {
        type: String,
        required: true,
        default: null
    },
    labels: {
        type: Array,
        required: true,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    }
});
const selected = ref(false);
const labels = computed(() => props.labels.filter(label => ! label.hide));
const reset = () => selected.value = null;
defineExpose({
    reset,
});
</script>
<style>
.summary {
    /*opacity: 0;*/
}

.summary .el-card__body {
    padding: 0;
}

.summary .summary-icon {
    width: 120px;
}

.summary .summary-text {
    height: 90px;
}
</style>
