<template>
  <app-form>
    <i-text
      v-if="! isLogged"
      v-model="form.email"
      :errors="errors('email')"
      :hint="Translator.t('help.we_will_send_you_your_order')"
      prepend-icon="mdi-email"
      name="email"
      class="mb-5"
    />

    <table>
      <thead>
        <tr>
          <th v-text="Translator.ta('type')" />
          <th v-text="Translator.ta('name')" />
          <th v-text="Translator.tl('actions')" />
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(product, index) in form.products || []"
          :key="index"
        >
          <th v-text="Translator.modelTitle(product.model)" />
          <th>
            <app-button
              v-if="product.id"
              :label="product.name"
              size="small"
              :route="CreationHelper.getCreationShowRoute(product)"
              class="d-block brand-font mx-auto"
            />
          </th>

          <th>
            <app-button
              :title="Translator.actionTitle('delete')"
              icon="mdi-delete"
              size="small"
              @click="$emit('remove', product)"
            />
          </th>
        </tr>
      </tbody>
    </table>
  </app-form>
</template>
<script setup>
import CreationHelper from '@/helpers/CreationHelper';
import useFormValidator from "@/composables/useFormValidator";
import rules from "@/components/forms/rules/cartRules";
import Translator from "@/objects/Translator";
import AppButton from '@/components/core/AppButton.vue';
import AppForm from '@/components/core/AppForm.vue';
import IText from '@/components/forms/inputs/iText.vue';
import { useAuth } from '@/stores/auth';
import { storeToRefs } from 'pinia';

defineEmits(['remove']);
const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});

const { isLogged } = storeToRefs(useAuth());
const { form, errors, validate, reset, setForm } = useFormValidator(props.modelValue, rules, ['products', 'email']);

defineExpose({ validate, reset, setForm });
</script>
