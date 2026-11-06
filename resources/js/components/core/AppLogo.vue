<template>
  <div class="d-flex flex-column justify-center align-center">
    <h4
      id="welcome"
      class="mini-title-brand mb-5"
      v-text="Translator.t('help.welcome_pre_header')"
    />
    <app-icon
      id="logo"
      icon="$brand_big"
      class="cursor-pointer"
      style="width: 128px; height: 128px"
      @click="onClick"
    />
    <h1
      id="title"
      v-text-split="'d-inline-block m-0 md:mx-1'"
      class="title-brand"
      @split="animateTitle"
      v-text="global.getAppName()"
    />
    <div
      id="separator"
      style="height: 2px; background: white; margin: auto; width: 300px"
    />
    <h4
      id="subtitle"
      class="subtitle-brand text-grey"
      v-text="Translator.t('help.welcome_subheader')"
    />
  </div>
</template>
<script setup>
import { gsap } from 'gsap';
import global from '@/config/global';
import Translator from '@/objects/Translator';
import { default as vTextSplit } from '@/directives/textSplit';
import { onMounted } from 'vue';
import AppIcon from '@/components/core/AppIcon.vue';

const animateTitle = () => {
    gsap.set('#title span', {
        transformOrigin: 'center center',
    });
    gsap.from('#title span', {
        opacity: 0,
        yPercent: -100,
        stagger: .1,
        rotate: -20
    });
}
const animateLogo = () => {
    gsap.set("#welcome, #logo #the_t, #logo #star, #subtitle, #separator", {
        transformOrigin: 'center center',
    });

    gsap.timeline()
        .from('#welcome', {
            opacity: 0,
            duration: 0.5
        })
        .from('#logo #the_t', {
            opacity: 0,
            scale: 0,
            // yPercent: -100,
            // ease: "elastic.out(1, 0.3)"
        })
        .from('#logo #star', {
            opacity: 0,
            scale: 0,
            duration: 1.5,
            ease: 'elastic.out(1, 0.4)',
            rotate: 360
        }, '<')
        .from('#logo #upper_right, #lower_right', {
            opacity: 0,
            xPercent: 100,
            ease: 'back'
        }, '-=1.25')
        .from('#logo #upper_left, #lower_left', {
            opacity: 0,
            xPercent: -100,
            ease: 'back'
        }, '-=1')
        .from('#separator', {
            opacity: 0,
            width: 0,
        })
        .from('#subtitle', {
            opacity: 0,
            yPercent: -50,
        });
}

const onClick = () => {
    animateTitle();
    animateLogo();
}

onMounted(() => animateLogo());

</script>
