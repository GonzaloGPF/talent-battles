<template>
  <app-form>
    <div class="grid grid-cols-3 mb-3 p-2 mx-auto space-x-2">
      <app-button
        v-for="(donationType, name) in donationTypes"
        :key="name"
        :icon="donationType.icon"
        :title="donationType.title"
        size="large"
        color="indigo"
        class="mx-auto"
        @click="onClick(name)"
      />
    </div>
    <i-money
      v-model="form.amount"
      :errors="errors('amount')"
      name="amount"
      class="max-w-lg"
    />
    <i-text
      v-model="form.message"
      :errors="errors('message')"
      name="message"
    />
  </app-form>
</template>
<script setup>
import rules from '@/components/forms/rules/donationRules';
import { computed } from "vue";
import Translator from "@/objects/Translator";
import useFormValidator from "@/composables/useFormValidator";
import IMoney from "@/components/forms/inputs/iMoney.vue";
import IText from "@/components/forms/inputs/iText.vue";
import AppButton from '@/components/core/AppButton.vue';
import AppForm from '@/components/core/AppForm.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, ['amount', 'message']);

const donationTypes = computed(() => ({
    coffee: {
        amount: 5,
        icon: 'mdi-coffee',
        title: Translator.tl('coffee'),
    },
    burger: {
        amount: 10,
        icon: 'mdi-food',
        title: Translator.tl('burger'),
    },
    meal: {
        amount: 20,
        icon: '$gift',
        title: Translator.tl('gift'),
    },
}));

const onClick = (name) => {
    const donationObj = donationTypes.value[name];

    form.amount = donationObj.amount;
    form.message = Translator.t('help.donating_a', { name: donationObj.title });
}

defineExpose({ validate, reset, setForm });
</script>
