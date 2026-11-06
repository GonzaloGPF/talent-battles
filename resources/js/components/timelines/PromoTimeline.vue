<template>
  <v-timeline
    :model-value="step"
    direction="horizontal"
    truncate-line="both"
    side="end"
    density="compact"
  >
    <v-timeline-item
      v-for="item in steps"
      :key="item.step"
      :dot-color="getColor(item)"
      :icon="item.icon"
      fill-dot
    >
      <div>
        <span
          style="cursor: default"
          v-text="item.title"
        />
        <v-tooltip
          :text="item.description"
          activator="parent"
          location="top"
        />
      </div>
    </v-timeline-item>
  </v-timeline>
</template>
<script setup>
const props = defineProps({
    steps: {
        type: Array,
        default: () => []
    },
    step: {
        type: Number,
        default: 0
    },
});

function getColor(item) {
    if (item.step === props.step) {
        return 'blue';
    }

    if (item.step < props.step) {
        return 'green'
    }

    return 'gray';
}
</script>
