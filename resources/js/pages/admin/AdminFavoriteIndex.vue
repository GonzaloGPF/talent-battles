<template>
  <div>
    <v-row class="mb-5">
      <v-col :cols="12" :md="6" :lg="3">
        <favorite-list-button
          class="w-100"
          @success="onSuccess"
        />
      </v-col>
    </v-row>
    <admin-favorites
      v-if="user"
      ref="adminTable"
      :params="{created_by: [user?.id]}"
    />
  </div>
</template>
<script setup>
import AdminFavorites from '@/components/admin/AdminFavorites.vue';
import FavoriteListButton from '@/components/buttons/FavoriteListButton.vue';
import { useAuth } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import { ref } from 'vue';

const { user } = storeToRefs(useAuth());
const adminTable = ref(null)

function onSuccess() {
    adminTable.value?.refreshTable();
}
</script>
