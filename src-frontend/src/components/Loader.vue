<script setup>
import {useLoaderStore} from "../stores/loader";

const loaderStore = useLoaderStore();
</script>

<template>
  <div v-if="loaderStore.isLoading" class="loader d-flex align-items-center justify-content-center">
    <div class="spinner-border text-dark" role="status">
    </div>
  </div>
</template>

<style scoped>
.loader{
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.3);
}
</style>