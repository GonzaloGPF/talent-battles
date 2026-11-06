<template>
  <div class="text-center md:text-left">
    <app-button
      :label="vTitle"
      :route="route"
      variant="text"
      class="text-center md:text-left text-blue !text-xl"
    />
    <!--
    <span
      v-if="subTitle"
      class="d-block text-center md:text-left text-xl mb-5 text-grey-lighten-2 !font-light my-2"
      v-text="subTitle"
    />
    -->
    <div style="height: 5px">
      <v-progress-linear
        :active="loading"
        indeterminate
      />
    </div>
    <v-progress-linear
      v-if="loopFetch"
      :model-value="loopProgress"
    />
    <track-list
      v-if="tracks.length"
      :tracks="tracks"
      class="mt-1"
    />
    <app-empty
      v-else
      model="track"
    />
  </div>
</template>
<script setup>
import TrackList from '@/components/lists/TrackList.vue';
import Translator from '@/objects/Translator';
import useHttp from '@/composables/useHttp';
import { computed, onBeforeUnmount, onMounted, ref, toRef, watch } from 'vue';
import { usePlayer } from '@/stores/player';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';
import TrackHelper from '@/helpers/TrackHelper';
import AppEmpty from '@/components/core/AppEmpty.vue';
import Utils from '@/objects/Utils';

const LOOP_INTERVAL = 10000;
const LOOP_STEP = 100;

const props = defineProps({
    title: {
        type: String,
        default: null
    },
    subTitle: {
        type: String,
        default: null
    },
    route: {
        type: Object,
        default: null
    },
    params: {
        type: Object,
        default: null
    },
    loopFetch: {
        type: Boolean,
        default: false
    }
});
const { getJson, loading } = useHttp();
const { playing, track } = storeToRefs(usePlayer());
const currentStep = ref(0);
const tracks = ref([]);
let fetchInterval = null;
const vTitle = computed(() => props.title || Translator.modelTitle('track', true));
const loopProgress = computed(() => 100 / LOOP_INTERVAL * currentStep.value);
const isPlaying = computed(() => playing && tracks.value.map(track => track.id).includes(track.id));
const propsParams = toRef(props, 'params');

const onSuccess = ({ data }) => tracks.value = data;

const makeRequest = () => {
    let params = {
        ...TrackHelper.indexParams,
        ...propsParams.value
    };

    return getJson('tracks', { params })
        .then(onSuccess);
}

watch(propsParams, (newValue, oldValue) => {
    if (Utils.areEquals(newValue, oldValue)) return;

    makeRequest();
}, { immediate: true, deep: true });

onMounted(() => {
    if (props.loopFetch) {
        fetchInterval = buildInterval();
    }
});

onBeforeUnmount(() => clearInterval(fetchInterval));

function buildInterval() {
    return setInterval(() => {
        if (isPlaying.value) return;

        currentStep.value += LOOP_STEP;

        if (currentStep.value === LOOP_INTERVAL) {
            makeRequest();
            currentStep.value = 0;
        }
    }, LOOP_STEP)
}
</script>
