<template>
  <app-button
    :label="Translator.tl('ask_for_votes')"
    prepend-icon="mdi-bullhorn"
    color="primary"
    @click="askForVotes"
  />
</template>
<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import AppButton from '@/components/core/AppButton.vue';
import Translator from '@/objects/Translator';
import Utils from '@/objects/Utils';
import { useFlashMessages } from '@/stores/flashMessages';

const props = defineProps({
    battle: {
        type: Object,
        required: true,
    },
    creation: {
        type: Object,
        required: true,
    },
});

const router = useRouter();
const { pushFlashMessage } = useFlashMessages();
const url = computed(() => Utils.getFullUrl(router.resolve({ name: 'battles.show', params: { id: props.battle.id } }).href));

/**
 * The share menu of the device reaches the apps people really use, like WhatsApp or Instagram. Where there is none,
 * the message is copied to paste anywhere.
 */
async function askForVotes() {
    const text = Translator.t('help.vote_for_me', { name: props.creation.name });

    if (navigator.share) {
        try {
            await navigator.share({ text, url: url.value });

            return;
        } catch (error) {
            // Closing the menu without sharing is not a failure
            if (error?.name === 'AbortError') return;
        }
    }

    await navigator.clipboard?.writeText(`${ text } ${ url.value }`);

    pushFlashMessage(Translator.t('help.vote_for_me_copied'));
}
</script>
