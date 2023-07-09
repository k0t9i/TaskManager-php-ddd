<script setup>
import AsyncProjectInfo from "../components/AsyncProjectInfo.vue";
</script>

<template>
  <h4 class="my-4">Project info</h4>
  <Suspense>
    <AsyncProjectInfo />
    <template #fallback>
      <span>
        <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
      </span>
    </template>
  </Suspense>
</template>