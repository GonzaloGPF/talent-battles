<template>
  <div>
    <h3
      v-if="!hideTitle"
      class="subtitle-brand"
      v-text="Translator.modelTitle('comment', true)"
    />

    <v-progress-linear
      :active="loading"
      indeterminate
    />

    <comment-reply
      :is-root="true"
      :model="model"
      class="mb-2"
      @new-comment="onNewComment"
      @delete="getComments"
    />

    <comments-list
      v-if="comments['null']"
      :model="model"
      :data="comments"
      :comments="comments['null']"
      :disabled="loading"
      @new-comment="onNewComment"
      @delete="getComments"
    />
  </div>
</template>
<script setup>
import CommentReply from '@/components/modules/comments/CommentReply.vue';
import useHttp from '@/composables/useHttp';
import { computed, onMounted, ref } from 'vue';
import _ from 'lodash';
import CommentsList from '@/components/modules/comments/CommentsList.vue';
import Translator from '@/objects/Translator';

const props = defineProps({
  model: {
    type: Object,
    default: null,
    required: true
  },
  hideTitle: {
    type: Boolean,
    default: false,
  }
});
const { loading, getJson } = useHttp();
const comments = ref({});
const params = computed(() => ({
  model_id: props.model?.id,
  model_type: props.model?.model,
  with: [
    'createdBy.image',
    'like',
    'comments.like',
    'comments.createdBy',
  ],
  counts: [
    'likes'
  ]
}));

const getComments = async () => {
  const { data } = await getJson('comments', { params: params.value });
  comments.value = _.groupBy(data, 'comment_id');
}

const onNewComment = (comment) => {
  let parentId = comment.comment_id || 'null';

  if (comments.value[parentId]) {
    comments.value[parentId].unshift(comment); // Add new comment to the group
  } else {
    const allComments = comments.value;
    allComments[parentId] = [comment] // New group of comments

    comments.value = allComments;
  }
}

onMounted(() => getComments());

</script>
