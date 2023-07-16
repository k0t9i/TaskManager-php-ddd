<script setup>
import AsyncEditTask from "../components/AsyncEditTask.vue";
</script>

<template>
  <Suspense>
    <AsyncEditTask />
    <template #fallback>
      <span>
        <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
      </span>
    </template>
  </Suspense>
</template>