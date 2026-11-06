<template>
  <app-button
    v-if="isLogged"
    :title="title"
    :loading="loading"
    :color="null"
    :disabled="! isLogged"
    :modal="modal"
    density="compact"
    icon="mdi-alert"
    size="small"
    variant="text"
    @click="showModal = true"
  >
    <template #modal>
      <app-alert
        :message="Translator.t('help.make_a_report')"
        border="start"
        variant="tonal"
      />

      <div class="p-3">
        <app-icon icon="mdi-alert mr-2" />
        <span v-text="reportingText" />
      </div>

      <report-form
        ref="form"
        v-model="form"
      />
    </template>
  </app-button>
</template>
<script setup>
import ReportForm from '@/components/forms/ReportForm.vue';
import { computed, ref } from 'vue';
import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';
import useHttp from '@/composables/useHttp';
import AppAlert from '@/components/core/AppAlert.vue';
import AppIcon from '@/components/core/AppIcon.vue';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';

const emit = defineEmits(['success']);
const props = defineProps({
    model: {
        type: Object,
        default: null
    }
});
const { postJson, loading } = useHttp();
const { isLogged } = storeToRefs(useAuth());
const modal = computed(() => ({
  data: {
    modelValue: showModal.value,
    loading: loading.value,
    title: title.value,
  },
  listeners: {
    close: () => showModal.value = false,
    ok: makeRequest,
  }
}));
const form = ref({});
const showModal = ref(false);
const title = computed(() => Translator.actionTitle('report'));
const reportingText = computed(() => {
    let name = props.model?.name;
    let modelName = props.model?.model;

    if (! name) {
        name = Translator.modelTitle(modelName);
    }

    if (name) {
        return Translator.t('help.reporting_model', {name});
    }

    return Translator.t('help.make_a_report');
});

function makeRequest() {
    const validated = form.value.validate(true);

    if (!validated) return;

    validated.append('model_id', props.model?.id);
    validated.append('model_type', props.model?.model);

    postJson('reports', validated)
        .then(onSuccess);
}

function onSuccess() {
    showModal.value = false;
    form.value = {};
    emit('success');
}

</script>
