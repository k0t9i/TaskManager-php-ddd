<script setup>
import loader from './loader';
console.log(loader.isShowing());
</script>

<template>
  <div v-if="loader.isShowing()" class="loader d-flex align-items-center justify-content-center">
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