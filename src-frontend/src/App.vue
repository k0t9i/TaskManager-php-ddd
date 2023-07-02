<script setup>
import {RouterView} from 'vue-router'
import Loader from "./components/Loader.vue";
import NavBar from "./components/NavBar.vue";
import {useAuthStore} from "./stores/auth";

const authStore = useAuthStore();
</script>

<template>
  <NavBar v-if="authStore.token" />
  <RouterView />
  <Loader />
</template>

<style scoped>
</style>
