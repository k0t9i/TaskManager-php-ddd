<script setup>
import AsyncProject from "../components/AsyncProject.vue";
</script>

<template>
  <div class="container-md">
    <Suspense>
      <AsyncProject />
      <template #fallback>
      <span>
        <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
      </span>
      </template>
    </Suspense>
  </div>
</template>

<style scoped>

</style>