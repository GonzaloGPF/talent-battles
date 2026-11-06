<template>
  <v-container>
    <app-field
      :label="Translator.ta('email')"
      :model-value="modelValue?.email"
      class="mb-2"
    />

    <app-field
      :label="Translator.ta('name')"
      :model-value="modelValue?.name"
      class="mb-5"
    />

    <app-field
      :label="Translator.ta('last_name')"
      :model-value="modelValue?.last_name"
      class="mb-5"
    />

    <app-field
      :label="Translator.ta('nick') + ' (aka)'"
      :model-value="modelValue?.nick"
      class="mb-5"
    />

    <!--
        <app-field
            :label="Translator.ta('phone')"
            :model-value="modelValue?.phone"
            class="mb-5"
        />
        -->

    <app-field
      :label="Translator.ta('gender')"
      :model-value="Translator.tConstName('gender', modelValue?.gender)"
      class="mb-5"
    />

    <app-field
      :label="Translator.ta('skills')"
      :model-value="modelValue?.skills"
      constant="skill"
      class="mb-5"
    />

    <app-field
      :label="Translator.ta('categories')"
      :model-value="modelValue?.categories"
      constant="category"
      class="mb-5"
    />

    <app-field
      :label="Translator.ta('birthdate')"
      :model-value="modelValue?.birthdate"
      type="date"
      class="mb-5"
    />

    <app-field
      :label="Translator.ta('quote')"
      :model-value="modelValue?.quote"
      class="mb-5"
    />

    <app-field
      :label="Translator.ta('bio')"
      :model-value="modelValue?.bio"
      class="mb-5"
    />

    <v-divider class="my-5" />

    <div class="mx-auto">
      <h3
        class="text-h5"
        v-text="Translator.tl('social')"
      />
      <social-info :model-value="modelValue?.social_settings" />
    </div>
  </v-container>
</template>
<script setup>
import Translator from '@/objects/Translator';
import SocialInfo from '@/components/info/SocialInfo.vue';
import AppField from '@/components/core/AppField.vue';

defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
</script>
