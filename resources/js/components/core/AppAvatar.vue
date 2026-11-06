<template>
  <div class="flex flex-column justify-center items-center">
    <v-avatar
      :image="imageUrl"
      :icon="icon"
      :size="size"
      :color="color"
      :rounded="tile ? '0' : null"
      :title="model?.name"
      :class="{'cursor-pointer': canEdit || navigate}"
      class="elevation-5"
      style="background: radial-gradient(black, transparent);"
      @click="onClick"
    >
      <v-img
        :color="color"
        :src="imageUrl"
        @error="imageError = true"
      />
    </v-avatar>
    <v-progress-linear
      v-if="loading"
      indeterminate
    />
    <i-file
      v-show="false"
      ref="fileElement"
      v-model="form.file"
      :accept="imageMimes"
      :disabled="loading"
      name="file"
      @update:model-value="onSubmit"
    />
    <validation-errors class="mx-auto mt-3" />
  </div>
</template>
<script setup>
import global from '@/config/global';
import IFile from '@/components/forms/inputs/iFile.vue';
import {computed, ref, toRefs} from 'vue';
import {useAuth} from '@/stores/auth';
import useHttp from '@/composables/useHttp';
import {useRoute, useRouter} from 'vue-router';
import files from '@/config/files';
import Utils from '@/objects/Utils';
import {storeToRefs} from 'pinia';
import Formatter from '@/objects/Formatter';
import ValidationErrors from '@/components/lists/ValidationErrors.vue'

const emit = defineEmits(['success']);
const props = defineProps({
  model: {
    type: Object,
    default: null
  },
  type: {
    type: String,
    default: null,
  },
  src: {
    type: String,
    default: null,
  },
  icon: {
    type: String,
    default: null,
  },
  size: {
    type: Number,
    default: 50,
  },
  tile: {
    type: Boolean,
    default: null
  },
  color: {
    type: String,
    default: null,
  },
  editable: {
    type: Boolean,
    default: false
  },
  navigate: {
    type: Boolean,
    default: false
  },
});
const {model} = toRefs(props);
const {push} = useRouter();
const {path} = useRoute()
const form = ref({
  file: null
});
const fileElement = ref(null);
const {postJson, loading} = useHttp();
const {user: me} = storeToRefs(useAuth());
const defaultUrl = global.getAppURL('/images/avatar.webp');
const imageMimes = files.imageMimes;
const imageError = ref(false);
const avatar = computed(() => model.value?.image);
const imageUrl = computed(() => {
  if (imageError.value) {
    return defaultUrl;
  }

  if (props.src) {
    return props.src;
  }

  if (!avatar.value || !imageMimes.includes(avatar.value.mime)) {
    return defaultUrl;
  }

  return Utils.getFileURL(avatar.value, path.includes('profile'));
});
const createdBy = computed(() => model.value?.created_by || avatar.value?.created_by);
const itsMine = computed(() => createdBy.value?.id === me.value?.id);
const canEdit = computed(() => props.editable || itsMine.value);
const onClick = () => {
  if (loading.value) return;

  if (props.navigate) {
    const creator = model.value?.createdBy || model.value;
    const target = Formatter.plural(creator.model);

    return push({
      name: `${target}.show`,
      params: {id: creator.id}
    })
  }

  if (canEdit.value) {
    fileElement.value?.click();
  }
}

const onSubmit = () => {
  if (avatar.value) {
    return update();
  }

  create();
}

const update = () => {
  form.value.file_type = 'image';
  form.value._method = 'PUT';

  postJson(`files/${avatar.value.id}`, Utils.toFormData(form.value))
    .then(onSuccess)
    .catch(onError);
}

const create = () => {
  form.value.file_type = 'image';
  form.value.model_type = Formatter.singular(props.type?.toLowerCase());
  form.value.model_id = props.model?.id;

  postJson('files', Utils.toFormData(form.value))
    .then(onSuccess)
    .catch(onError);
}

const onSuccess = () => {
  form.value = {};
  emit('success');
}

const onError = () => {
  form.value.file = null;
  fileElement.value?.reset();
}
</script>
