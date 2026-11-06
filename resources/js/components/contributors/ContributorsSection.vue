<template>
  <v-container>
    <h1 class="brand-font text-h5" v-text="Translator.t('texts.contributors_title')" />

    <p
      class="p-1"
      v-text="Translator.t('texts.contributors_text')"
    />

    <app-alert
      class="my-3 max-w-2xl"
      border="start"
      variant="tonal"
    >
      <div class="d-flex align-center justify-space-around">
        <span v-text="Translator.t('texts.contributors_text_2')" />
        <donation-button />
      </div>
    </app-alert>

    <v-progress-linear
      v-if="loading"
      indeterminate
    />

    <div>
      <v-card
        v-for="donation in donations"
        :key="donation.id"
        color="light-blue-darken-4"
        elevation="6"
        class="contributor-cards p-5 border rounded-lg d-inline-block mx-4 my-4 opacity-0"
      >
        <v-card-text class="text-center">
          <donation-info :model-value="donation" />
        </v-card-text>
      </v-card>
    </div>
  </v-container>
</template>
<script setup>
import DonationInfo from '@/components/info/DonationInfo.vue';
import DonationButton from '@/components/buttons/DonationButton.vue';
import Translator from '@/objects/Translator';
import useHttp from '@/composables/useHttp';
import { onMounted, ref } from 'vue';
import AppAlert from '@/components/core/AppAlert.vue';
import { gsap } from 'gsap';

const { loading, getJson } = useHttp();
const donations = ref([]);
const params = {
    with: ['createdBy'],
    paymentState: ['success'],
    randomize: true,
    per_page: 50
    // limit: 10
};

const makeRequest = () => {
    return getJson('donations', { params })
        .then(({ data }) => donations.value = data);
}

const animateCards = () => {
    if (! donations.value?.length) return;

    gsap.fromTo('.contributor-cards', {
        opacity: 0,
        // yPercent: 100,
        transformOrigin: 'center center',
        rotate: 'random(-90, 90)',
        scale: 3
    }, {
        opacity: 1,
        // yPercent: 0,
        stagger: .1,
        delay: 'random(0, 1)',
        rotate: 'random(-10, 10)',
        ease: 'circ.in',
        duration: 0.75,
        scale: 1
    });
}

onMounted(async () => {
    await makeRequest();
    animateCards();
});

</script>
