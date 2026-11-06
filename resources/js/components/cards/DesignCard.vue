<template>
  <BaseCard
    :model="design"
    :actions="actions"
    :chips="chips"
    :image-style="{'max-height': '300px'}"
    class="base-card rounded-xl"
    @chip="$emit('style', $event.value)"
    @image="$emit('image', imageUrl)"
    @action="onAction"
  >
    <div class="d-flex flex-col md:flex-row justify-space-between justify-center align-center">
      <app-field
        v-if="design?.created_by"
        :model-value="design?.createdBy?.nick_name"
        :route-data="{name: 'users.show', params: {id: design?.created_by}}"
        size="small"
        color="grey lighten-1"
      />
      <app-field
        v-if="design?.id"
        :model-value="design?.name"
        :route-data="DesignHelper.showRoute(design)"
        icon="$design"
        size="small"
        class="brand-font"
      />
    </div>

    <template #info>
      <DesignInfo
        v-if="design?.id"
        :model-value="design"
      />
    </template>
  </BaseCard>
</template>
<script setup>
import {computed, defineAsyncComponent} from 'vue';
import Translator from '@/objects/Translator';
import Utils from '@/objects/Utils';
import DesignHelper from '@/helpers/DesignHelper';

defineEmits([
    'style',
    'image',
]);
const props = defineProps({
    design: {
        type: Object,
        required: true,
        default: null
    },
    actions: {
        type: Array,
        default: () => []
    },
});
const BaseCard = defineAsyncComponent(() => import('@/components/cards/BaseCard.vue'));
const AppField = defineAsyncComponent(() => import('@/components/core/AppField.vue'));
const DesignInfo = defineAsyncComponent(() => import('@/components/info/DesignInfo.vue'));
const imageUrl = computed(() => Utils.getFileURL(props.design?.image));
const chips = computed(() => [
    {
        name: 'style',
        value: props.design?.style,
        label: Translator.tConstName('style', props.design?.style),
    },
    // {
    //     name: 'thematic',
    //     label: Translator.tConstName('thematic', props.design?.thematic),
    // },
].filter(v => v.label));
const onAction = () => {
};
</script>
