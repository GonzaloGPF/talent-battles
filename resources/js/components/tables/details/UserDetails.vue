<template>
  <v-container fluid>
    <v-progress-linear
      :active="loading"
      indeterminate
    />
    <v-row justify="center">
      <v-col :cols="12" :md="6">
        <user-card
          :user="vModel"
          class="grey darken-4"
        />
      </v-col>
      <v-col :cols="12" :md="6">
        <user-info :model-value="vModel" />
      </v-col>
    </v-row>
  </v-container>
</template>
<script setup>
import UserInfo from '@/components/info/UserInfo.vue';
import UserCard from '@/components/cards/UserCard.vue';
import useDetails from '@/composables/useDetails';
import UserHelper from '@//helpers/UserHelper';

const props = defineProps({
    modeIdl: {
        type: Number,
        default: null
    }
});
const { loading, vModel } = useDetails(props.modelId, 'users', UserHelper.showParams);
</script>
