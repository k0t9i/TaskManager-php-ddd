<script setup>

import AsyncAllProjectsList from "../components/AsyncAllProjectsList.vue";
</script>

<template>
  <div class="container-md">
    <h3 class="mt-4">Available projects</h3>
    <Suspense>
      <AsyncAllProjectsList />
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