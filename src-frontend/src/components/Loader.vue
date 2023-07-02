<script setup>
import loader from './loader';
</script>

<template>
  <div v-if="loader.isShowing()" class="loader d-flex align-items-center justify-content-center">
    <div class="spinner-border text-dark" role="status">
    </div>
  </div>
</template>
