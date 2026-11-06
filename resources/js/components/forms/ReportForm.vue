<template>
  <app-form>
    <div v-if="!solving">
      <i-text-area
        v-model="form.message"
        :errors="errors('message')"
        name="message"
        class="mb-3"
      />
      <i-file
        v-if="isCreating"
        v-model="form.file"
        :errors="errors('file')"
        :accept="files.imageMimes"
        :hint="Translator.t('help.report_file')"
        persistent-hint
        name="file"
      />
    </div>
    <div v-if="solving">
      <i-switch
        v-model="form.closed"
        :errors="errors('closed')"
        name="closed"
      />
      <i-text
        v-model="form.resolution"
        :errors="errors('resolution')"
        name="resolution"
      />
    </div>
  </app-form>
</template>
<script setup>
import rules from '@/components/forms/rules/reportRules';
import files from '@/config/files';
import useFormValidator from '@/composables/useFormValidator';
import Translator from '@/objects/Translator';
import ITextArea from '@/components/forms/inputs/iTextArea.vue';
import IFile from '@/components/forms/inputs/iFile.vue';
import IText from '@/components/forms/inputs/iText.vue';
import AppForm from '@/components/core/AppForm.vue';
import ISwitch from '@/components/forms/inputs/iSwitch.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  },
  solving: {
    type: Boolean,
    default: false
  }
});
const { form, validate, errors, isCreating, reset, setForm } = useFormValidator(props.modelValue, rules, [
  'message',
  'file',
  'resolution',
  'closed'
]);

defineExpose({ validate, reset, setForm });
</script>
