<script setup>
import {RouterView} from 'vue-router'
import NavBar from "./components/NavBar.vue";
import {useAuthStore} from "./stores/auth";

const authStore = useAuthStore();
</script>

<template>
  <NavBar v-if="authStore.token" />
  <RouterView />
</template>

<style scoped>
</style>
