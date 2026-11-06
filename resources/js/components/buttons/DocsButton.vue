<template>
  <app-button
    :title="Translator.tl('help')"
    icon="$help"
    variant="plain"
    class="animate__animated animate__fadeIn animate__slower"
    @click="goToDoc"
  />
</template>
<script setup>
import AppButton from '@/components/core/AppButton.vue';
import Translator from '@/objects/Translator';
import useDocsButton from '@/composables/useDocsButton';

const props = defineProps({
    path: {
        type: String,
        required: true,
        default: null
    },
});
const { goToDoc } = useDocsButton(props.path);
</script>
