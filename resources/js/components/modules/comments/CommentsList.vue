<template>
  <ul
    v-if="comments.length"
    class="list-none"
  >
    <li
      v-for="comment in comments"
      :key="comment.id"
      class="d-flex my-3 w-100"
    >
      <app-avatar
        :model="comment.createdBy"
        :size="64"
        navigate
        type="user"
      />
      <div class="flex-grow-1 max-w-lg">
        <comment-info :model-value="comment" />

        <comment-reply
          :model="model"
          :disabled="disabled"
          :comment="comment"
          class="comment-reply"
          @delete="$emit('delete', $event)"
          @new-comment="$emit('newComment', $event)"
        />

        <comments-list
          v-if="data[comment.id]"
          :model="model"
          :data="data"
          :comments="data[comment.id]"
          :disabled="disabled"
          class="pl-3"
          @edit="$emit('edit', $event)"
          @delete="$emit('delete', $event)"
          @new-comment="$emit('newComment', $event)"
        />
      </div>
    </li>
  </ul>
  <app-empty v-else />
</template>
<script setup>
import CommentInfo from '@/components/info/CommentInfo.vue';
import CommentReply from '@/components/modules/comments/CommentReply.vue';
import AppAvatar from '@/components/core/AppAvatar.vue';
import AppEmpty from '@/components/core/AppEmpty.vue';

defineEmits([
  'edit',
  'delete',
  'newComment',
]);
defineProps({
  data: {
    type: Object,
    default: () => {
    }
  },
  comments: {
    type: Array,
    default: () => []
  },
  model: {
    type: Object,
    default: null,
    required: true
  },
  disabled: {
    type: Boolean,
    default: false
  }
});
</script>
