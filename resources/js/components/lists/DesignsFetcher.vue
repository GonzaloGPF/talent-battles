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
    <design-list
      v-if="designs.length"
      :designs="designs"
      class="mt-1"
    />
    <app-empty
      v-else
      model="design"
    />
  </div>
</template>
<script setup>
import DesignList from '@/components/lists/DesignList.vue';
import { computed, onBeforeUnmount, onMounted, ref, toRefs, watch } from 'vue';
import Translator from '@/objects/Translator';
import useHttp from '@/composables/useHttp';
import AppButton from '@/components/core/AppButton.vue';
import DesignHelper from '@/helpers/DesignHelper';
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
const currentStep = ref(0);
const designs = ref([]);
let fetchInterval = null;
const vTitle = computed(() => props.title || Translator.modelTitle('design', true));
const loopProgress = computed(() => 100 / LOOP_INTERVAL * currentStep.value);
const { params: propsParams } = toRefs(props);

onMounted(() => {
    if (props.loopFetch) {
        fetchInterval = buildInterval();
    }
});

onBeforeUnmount(() => clearInterval(fetchInterval));

function buildInterval () {
    return setInterval(() => {
        currentStep.value += LOOP_STEP;

        if (currentStep.value === LOOP_INTERVAL) {
            makeRequest();
            currentStep.value = 0;
        }
    }, LOOP_STEP)
}

function makeRequest () {
    const params = {
        ...DesignHelper.indexParams,
        ...propsParams.value
    };

    return getJson('designs', { params })
        .then(onSuccess);
}
const onSuccess = ({ data }) => designs.value = data;

watch(propsParams, (newValue, oldValue) => {
    if (Utils.areEquals(newValue, oldValue)) return;

    makeRequest();
}, { immediate: true, deep: true });
</script>
