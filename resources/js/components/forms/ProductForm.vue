<template>
  <app-form>
    <app-alert
      v-if="model"
      :message="Translator.t('help.upload_file_to_sell')"
      density="compact"
      variant="tonal"
      class="mb-1"
    />

    <div class="d-flex">
      <i-money
        v-model="form.price"
        :errors="errors('price')"
        name="price"
        class="mr-6"
      />
      <i-switch
        v-model="form.exclusive"
        :errors="errors('exclusive')"
        :hint="Translator.t('help.exclusive')"
        name="exclusive"
      />
    </div>

    <i-file
      v-model="form.file"
      :errors="errors('file')"
      :accept="CreationHelper.isTrack(model) ? files.audioMimes : files.imageMimes"
      name="file"
    />
  </app-form>
</template>
<script setup>
import rules from '@/components/forms/rules/productRules';
import files from '@/config/files';
import Translator from '@/objects/Translator';
import useFormValidator from '@/composables/useFormValidator';
import CreationHelper from '@/helpers/CreationHelper';
import IMoney from '@/components/forms/inputs/iMoney.vue';
import ISwitch from '@/components/forms/inputs/iSwitch.vue';
import IFile from '@/components/forms/inputs/iFile.vue';
import AppForm from '@/components/core/AppForm.vue';
import AppAlert from '@/components/core/AppAlert.vue';

const props = defineProps({
    model: {
        type: Object,
        default: null
    }
});

const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, ['price', 'exclusive', 'file']);
defineExpose({ validate, reset, setForm });
</script>
