<template>
  <div>
    <div class="d-flex items-center align-center">
      <reaction-buttons
        v-if="!isRoot && comment"
        :model="comment"
        :actions="actions"
        class="max-w-5xs mt-2"
        @delete="onDelete"
      />

      <app-button
        v-if="! commenting"
        :label="isRoot ? Translator.actionTitle('add', 'comment') : Translator.actionTitle('reply')"
        :color="isRoot ? 'primary' : null"
        :disabled="! isLogged"
        variant="text"
        size="small"
        class="mt-1"
        @click="commenting = true"
      />
    </div>

    <div v-if="commenting">
      <comment-form ref="formElement" />

      <app-buttons
        :buttons="['cancel', 'send']"
        :loading="loading"
        :disabled="disabled"
        class="mt-2"
        size="small"
        @click="onClick"
      />
    </div>
  </div>
</template>
<script setup>
import CommentForm from '@/components/forms/CommentForm.vue';
import { useAuth } from '@/stores/auth';
import { computed, ref } from 'vue';
import Translator from '@/objects/Translator';
import useHttp from '@/composables/useHttp';
import ReactionButtons from '@/components/buttons/ReactionButtons.vue';
import AppButton from '@/components/core/AppButton.vue';
import AppButtons from '@/components/core/AppButtons.vue';
import { storeToRefs } from 'pinia';
import { useConfirm } from '@/stores/confirm';

const emits = defineEmits(['new-comment', 'delete']);
const props = defineProps({
  model: {
    type: Object,
    default: null
  },
  isRoot: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  comment: {
    type: Object,
    default: null
  }
});
const { isLogged, user } = storeToRefs(useAuth());
const { postJson, deleteJson, loading } = useHttp();
const { showConfirm } = useConfirm();
const commenting = ref(false);
const formElement = ref({});

const actions = computed(() => {
  const actions = ['like'];

  if (props.comment.created_by === user.value?.id) {
    actions.push('delete');
  }

  return actions;
})

const onClick = (event) => {
  if (event === 'cancel') {
    commenting.value = false;
    return;
  }

  submit();
}

async function submit() {
  const validated = formElement.value.validate(true);

  if (! validated) return;

  validated.append('model_id', props.model?.id);
  validated.append('model_type', props.model?.model);

  if (props.comment?.id) {
    validated.append('comment_id', props.comment?.id);
  }

  const { data } = await postJson('comments', validated);

  commenting.value = false;
  emits('new-comment', data);
  // formElement.value.reset();
}

async function onDelete() {
  const accept = await showConfirm(Translator.t('help.delete_text'));

  if (! accept) return;

  await deleteJson(`comments/${ props.comment?.id }`);

  emits('delete');
}
</script>
