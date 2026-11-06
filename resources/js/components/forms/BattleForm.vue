<template>
  <app-form class="d-flex flex-column align-center">
    <i-radio-group
      v-if="! opponent"
      v-model="type"
      :title="Translator.ta('type')"
      name="type"
    >
      <i-radio
        :label="Translator.modelTitle('track')"
        value="track"
        name="track_id"
        class="mr-5"
      />
      <i-radio
        :label="Translator.modelTitle('design')"
        value="design"
        name="design_id"
        class="mr-5"
      />
    </i-radio-group>

    <i-auto-complete
      v-if="isTrack"
      v-model="form.creation_id"
      v-model:selected="selected"
      :params="trackParams"
      :errors="errors('creation_id')"
      name="track_id"
      src="tracks"
      class="min-w-xs"
    />
    <i-auto-complete
      v-if="isDesign"
      v-model="form.creation_id"
      v-model:selected="selected"
      :params="designParams"
      :errors="errors('creation_id')"
      name="design_id"
      src="designs"
      class="min-w-xs"
    />
  </app-form>
</template>
<script setup>
import rules from '@/components/forms/rules/battleRules';
import { useAuth } from '@/stores/auth';
import { computed, ref, toRef, watch } from 'vue';
import CreationHelper from '@/helpers/CreationHelper';
import Translator from '@/objects/Translator';
import useFormValidator from '@/composables/useFormValidator';
import IRadio from '@/components/forms/inputs/iRadio.vue';
import IRadioGroup from '@/components/forms/inputs/iRadioGroup.vue';
import IAutoComplete from '@/components/forms/inputs/iAutoComplete.vue';
import { storeToRefs } from 'pinia';
import AppForm from '@/components/core/AppForm.vue';

const emit = defineEmits(['update:selected']);
const props = defineProps({
    modelValue: {
        type: Object,
        default: null,
    },
    opponent: {
        type: Object,
        default: null
    }
});
const { user } = storeToRefs(useAuth());
const { form, validate, errors, reset, setForm  } = useFormValidator(props.modelValue, rules, ['creation_id']);
const selected = ref(null);
const opponent = toRef(props, 'opponent');
const type = ref(null);
const isTrack = computed(() => {
    if (opponent.value) {
        return CreationHelper.isTrack(opponent.value);
    }
    return type.value === 'track';
});
const isDesign = computed(() => {
    if (opponent.value) {
        return CreationHelper.isDesign(opponent.value);
    }
    return type.value === 'design';
});
const trackParams = computed(() => {
    let params = {
        created_by: [user.value?.id],
        without_open_battles: true,
        can_challenge: true,
    }

    if (opponent.value) {
        params.track_types = [opponent.value?.track_type];
    }

    return params;
});
const designParams = computed(() => {
    let params = {
        created_by: [user.value?.id],
        without_open_battles: true,
        can_challenge: true,
    }

    if (opponent.value) {
        params.design_types = [opponent.value?.design_type]
    }

    return params;
});

watch(selected, () => emit('update:selected', selected.value), { deep: true, immediate: true });

defineExpose({ validate, reset, setForm });
</script>
