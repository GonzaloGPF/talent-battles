<template>
  <app-form>
    <v-row>
      <v-col :cols="12" :md="6" :lg="4">
        <i-select
          v-model="form.design_type"
          :errors="errors('design_type')"
          required
          name="design_type"
          src="design_types"
          class="max-w-sm"
        />
      </v-col>
      <v-col :cols="12" :md="6" :lg="4">
        <i-text
          v-model="form.name"
          :errors="errors('name')"
          required
          name="name"
        />
      </v-col>
      <v-col :cols="12" :md="6" :lg="4">
        <i-switch
          v-model="form.can_challenge"
          :errors="errors('can_challenge')"
          :hint="Translator.t('help.can_challenge')"
          name="can_challenge"
        />
      </v-col>
    </v-row>
    <i-switch
      v-model="expanded"
      name="extra_data"
      class="!flex"
    />

    <div v-if="expanded">
      <v-row>
        <v-col :cols="12" :md="6" :lg="4">
          <i-auto-complete
            v-model="form.album_id"
            :errors="errors('album_id')"
            :params="{created_by: [user?.id]}"
            name="album_id"
            src="albums"
          />
        </v-col>
        <v-col :cols="12" :md="6" :lg="4">
          <i-combo-box
            v-model="form.tags"
            :errors="errors('tags')"
            :label="Translator.modelTitle('tag', true)"
            item-value="name"
            name="tags"
            src="tags"
            multiple
          />
        </v-col>
        <v-col :cols="12" :md="6" :lg="4">
          <i-color
            v-model="form.color"
            :errors="errors('color')"
            name="color"
            hide-inputs
          />
        </v-col>
      </v-row>

      <v-row>
        <v-col :cols="12" :md="6" :lg="4">
          <i-select
            v-model="form.aspect_ratio"
            :errors="errors('aspect_ratio')"
            name="aspect_ratio"
            src="aspect_ratios"
          />
        </v-col>
        <v-col :cols="12" :md="6" :lg="4">
          <i-select
            v-model="form.style"
            :errors="errors('style')"
            name="style"
            src="styles"
          />
        </v-col>
      </v-row>

      <i-text
        v-model="form.description"
        :errors="errors('description')"
        name="description"
      />
    </div>

    <div v-if="! exclusiveSold">
      <i-switch
        v-if="isCreating"
        v-model="form.battle"
        :errors="errors('battle')"
        :disabled="!form.design_type || !form.can_challenge || !canAffordBattleCreation"
        :hint="Translator.t('help.create_battle', {points: createBattlePoints})"
        name="battle"
        class="mb-2 max-w-2xs"
      />
      <fieldset class="px-2">
        <legend v-text="Translator.modelTitle('file')" />

        <app-alert
          :message="Translator.t('help.upload_tagged_file')"
          variant="tonal"
          class="mb-2"
          density="compact"
        />

        <i-file
          v-model="form.image"
          :errors="errors('image')"
          :accept="files.imageMimes"
          name="image"
        />
      </fieldset>
    </div>
    <div v-if="! exclusiveSold && isCreating && user?.is_account_ready">
      <i-switch
        v-model="form.sell"
        :errors="errors('sell')"
        :disabled="!form.design_type"
        name="sell"
        class="mb-2"
      />
      <fieldset v-if="form.sell" class="px-2">
        <legend v-text="Translator.modelTitle('product')" />
        <app-alert
          :show="! form.auto_tag"
          :message="Translator.t('help.upload_file_to_sell')"
          variant="tonal"
          density="compact"
          class="mb-2"
        />
        <i-money
          v-model="form.price"
          :errors="errors('price')"
          name="price"
          class="mr-2"
        />
        <i-switch
          v-model="form.exclusive"
          :errors="errors('exclusive')"
          :hint="Translator.t('help.exclusive')"
          name="exclusive"
          class="mr-3"
        />
        <i-switch
          v-model="form.auto_tag"
          :errors="errors('exclusive')"
          :hint="Translator.t('help.auto_tag')"
          name="auto_tag"
        />
        <i-file
          v-if="!form.auto_tag"
          v-model="form.file_to_sell"
          :errors="errors('file_to_sell')"
          :accept="files.imageMimes"
          name="file_to_sell"
        />
      </fieldset>
    </div>
    <app-alert
      v-if="exclusiveSold"
      :message="Translator.t('help.exclusive_sold')"
      variant="text"
    />
  </app-form>
</template>
<script setup>
import { computed, ref } from 'vue';
import rules from '@/components/forms/rules/designRules';
import files from '@/config/files';
import useFormValidator from '@/composables/useFormValidator';
import Translator from '@/objects/Translator';
import ISelect from '@/components/forms/inputs/iSelect.vue';
import IComboBox from '@/components/forms/inputs/iComboBox.vue';
import IText from '@/components/forms/inputs/iText.vue';
import IColor from '@/components/forms/inputs/iColor.vue';
import IAutoComplete from '@/components/forms/inputs/iAutoComplete.vue';
import ISwitch from '@/components/forms/inputs/iSwitch.vue';
import AppAlert from '@/components/core/AppAlert.vue';
import IMoney from '@/components/forms/inputs/iMoney.vue';
import { useAuth } from '@/stores/auth';
import IFile from '@/components/forms/inputs/iFile.vue';
import AppForm from '@/components/core/AppForm.vue';
import { storeToRefs } from 'pinia';
import DesignHelper from '@/helpers/DesignHelper';
import { useCompany } from '@/stores/company';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    },
});
const { user } = storeToRefs(useAuth());
const { createBattlePoints } = storeToRefs(useCompany());
const canAffordBattleCreation = computed(() => user.value?.points > createBattlePoints.value);
const {
    validate,
    errors,
    form,
    isCreating,
    reset,
    setForm
} = useFormValidator(props.modelValue, rules, DesignHelper.inputNames);
const exclusiveSold = computed(() => props.modelValue?.is_exclusive_sold);
const expanded = ref(false);

defineExpose({ validate, reset, setForm });
</script>
