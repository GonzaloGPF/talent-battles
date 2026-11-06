<template>
  <app-form>
    <i-file
      v-model="form.image"
      :errors="errors('image')"
      :accept="files.imageMimes"
      name="image"
    />
    <v-row>
      <v-col :cols="12" :md="4">
        <i-text
          v-model="form.name"
          :errors="errors('name')"
          name="name"
        />
      </v-col>
      <v-col :cols="12" :md="4">
        <i-combo-box
          v-model="form.tags"
          :errors="errors('tags')"
          :label="Translator.modelTitle('tag', true)"
          name="tags"
          src="tags"
          multiple
        />
      </v-col>
      <v-col :cols="12" :md="4">
        <i-date
          v-model="form.released"
          :errors="errors('released')"
          name="released"
        />
      </v-col>
    </v-row>

    <i-text-area
      v-model="form.description"
      :errors="errors('description')"
      name="description"
    />
  </app-form>
</template>
<script setup>
import rules from '@/components/forms/rules/albumRules';
import Translator from '@/objects/Translator';
import useFormValidator from '@/composables/useFormValidator';
import IText from '@/components/forms/inputs/iText.vue';
import IComboBox from '@/components/forms/inputs/iComboBox.vue';
import ITextArea from '@/components/forms/inputs/iTextArea.vue';
import AppForm from '@/components/core/AppForm.vue';
import files from '@/config/files';
import IFile from '@/components/forms/inputs/iFile.vue';
import IDate from '@/components/forms/inputs/iDate.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const { form, validate, errors, reset, setForm } = useFormValidator(props.modelValue, rules, [
    'name',
    'tags',
    'released',
    'description',
]);
defineExpose({ validate, reset, setForm });
</script>
