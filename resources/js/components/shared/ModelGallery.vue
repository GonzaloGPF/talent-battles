<template>
  <app-modal
    :model-value="!!currentModel"
    :hide-actions="true"
    height="100%"
    @keydown.esc.stop="$emit('close')"
    @close="$emit('close')"
  >
    <v-carousel
      v-model="index"
      :hide-delimiters="true"
    >
      <v-carousel-item
        v-for="model in vModels"
        :key="model.id"
      >
        <div>
          <app-image
            :file="model.image"
            :contain="true"
            :height="smAndDown ? '400' : '450'"
            class="align-end text-white"
            backdrop
          />
          <div class="d-flex flex-col md:flex-row align-center justify-center md:justify-space-around md:space-x-3">
            <app-field
              :model-value="currentModel?.createdBy?.nick_name"
              color="grey lighten-1"
              icon="mdi-account"
              :route-data="{name: 'users.show', params: {id: currentModel?.createdBy.id }}"
            />
            <app-field
              :model-value="currentModel?.name"
              :icon="CreationHelper.getCreationIcon(currentModel)"
              :color="currentModel?.color"
              :route-data="{name: modelRouteName, params: {id: currentModel?.id }}"
            />
          </div>
        </div>
      </v-carousel-item>
    </v-carousel>
  </app-modal>
</template>
<script setup>
import { computed, ref, toRef, watch } from 'vue';
import AppModal from '@/components/core/AppModal.vue';
import AppImage from '@/components/core/AppImage.vue';
import CreationHelper from '@/helpers/CreationHelper';
import AppField from '@/components/core/AppField.vue';
import Formatter from '@/objects/Formatter';
import { useDisplay } from 'vuetify';

defineEmits(['close']);
const props = defineProps({
    selectedId: {
        type: Number,
        default: null
    },
    models: {
        type: Array,
        default: () => []
    },
});
const { smAndDown } = useDisplay();
const index = ref(null);
const vModels = toRef(props, 'models');
const selectedIdProp = toRef(props, 'selectedId');
const currentModel = computed(() => vModels.value?.[index.value]);
const modelRouteName = computed(() => Formatter.plural(currentModel.value?.model) + '.show');

watch(selectedIdProp, () => {
    if (! vModels.value) return;

    const model = vModels.value.find(model => model.id === selectedIdProp.value);
    index.value = vModels.value.indexOf(model);
});
</script>
