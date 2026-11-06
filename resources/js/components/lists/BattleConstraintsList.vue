<template>
  <v-list
    :items="items"
    density="compact"
    elevation="6"
    rounded
  />
</template>
<script setup>
import { computed } from 'vue';
import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';

const props = defineProps({
    isTrack: {
        type: Boolean,
        default: null
    },
    minPlay: {
        type: Number,
        default: null
    }
});
const { isLogged } = useAuth()
const items = computed(() => {
    const items = [
        {
            type: 'subheader',
            title: Translator.t('help.to_vote'),
        }
    ];

    if (! isLogged.value) {
        items.push({
            title: Translator.t('help.must_be_logged_to_vote'),
            props: {
                class: 'text-caption'
            }
        });
    }

    if (props.isTrack) {
        items.push({
            title: Translator.t('help.must_listen_seconds', { seconds: props.minPlay }),
            props: {
                class: 'text-caption'
            }
        })
    }

    return items;
})
</script>
