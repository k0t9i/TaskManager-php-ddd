<script setup>
import {RouterView} from 'vue-router'
import NavBar from "./components/NavBar.vue";
import {useAuthStore} from "./stores/auth";
import ConfirmModal from "./components/ConfirmModal.vue"
import {useQueryStore} from "./stores/queryStore";

const authStore = useAuthStore();
const queryStore = useQueryStore();
queryStore.watch();
</script>

<template>
  <NavBar v-if="authStore.token" />
  <RouterView />
  <ConfirmModal />
</template>

<style scoped>
</style>
