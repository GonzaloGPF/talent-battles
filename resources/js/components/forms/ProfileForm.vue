<template>
  <app-form>
    <i-text
      v-model="form.email"
      :errors="errors('email')"
      name="email"
    />

    <i-text
      v-model="form.name"
      :errors="errors('name')"
      name="name"
    />

    <i-text
      v-model="form.last_name"
      :errors="errors('last_name')"
      name="last_name"
    />

    <i-text
      v-model="form.nick"
      :errors="errors('nick')"
      :label="Translator.ta('nick') + ' (aka)'"
      name="nick"
    />

    <!--
        <i-text
            v-model="form.phone"
            :errors="errors('phone')"
            name="phone"
        />
        -->

    <i-select
      v-model="form.gender"
      :errors="errors('gender')"
      :hint="Translator.t('help.not_on_public_profile')"
      persistent-hint
      name="gender"
      src="genders"
      class="max-w-sm"
    />

    <i-select
      v-model="form.skills"
      :errors="errors('skills')"
      name="skills"
      src="skills"
      class="max-w-sm"
      multiple
    />

    <i-select
      v-model="form.categories"
      :errors="errors('categories')"
      name="categories"
      src="categories"
      class="max-w-sm"
      multiple
    />

    <i-date
      v-model="form.birthdate"
      :errors="errors('birthdate')"
      :hint="Translator.t('help.not_on_public_profile')"
      persistent-hint
      name="birthdate"
    />

    <i-text
      v-model="form.quote"
      name="quote"
    />

    <i-text-area
      v-model="form.bio"
      name="bio"
      class="mb-10"
    />

    <v-divider class="my-5" />

    <fieldset class="px-2">
      <legend v-text="Translator.tl('social')" />
      <social-form v-model="form.social_settings" />
    </fieldset>
  </app-form>
</template>
<script setup>
import rules from '@/components/forms/rules/profileRules';
import SocialForm from '@/components/forms/SocialForm.vue';
import useFormValidator from '@/composables/useFormValidator';
import Translator from '@/objects/Translator';
import ITextArea from '@/components/forms/inputs/iTextArea.vue';
import IText from '@/components/forms/inputs/iText.vue';
import AppForm from '@/components/core/AppForm.vue';
import ISelect from '@/components/forms/inputs/iSelect.vue';
import UserHelper from '@/helpers/UserHelper';
import IDate from '@/components/forms/inputs/iDate.vue';
// import IDate from '@/components/forms/inputs/iDate.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  }
});
const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, UserHelper.inputNames);

defineExpose({ validate, reset, setForm });
</script>
