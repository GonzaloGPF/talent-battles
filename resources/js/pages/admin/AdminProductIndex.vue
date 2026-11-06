<template>
  <div>
    <!--
    <v-row class="mb-5">
      <v-col :cols="12" :md="6" :lg="3">
        <promo-product-button class="w-100" />
      </v-col>
    </v-row>
    -->
    <admin-products
      ref="tableElement"
      :params="params"
    />
  </div>
</template>
<script setup>
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';
import { computed, ref } from 'vue';
import AdminProducts from '@/components/admin/AdminProducts.vue';
// import PromoProductButton from '@/components/buttons/PromoProductButton.vue';

const { user } = storeToRefs(useAuth());
const tableElement = ref(null);
const params = computed(() => ({
  created_by: [user.value?.id],
  with: ['file']
}));
</script>
