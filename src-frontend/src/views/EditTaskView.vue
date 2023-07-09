<script setup>
import AsyncEditTask from "../components/AsyncEditTask.vue";
</script>

<template>
  <h4 class="my-4">Edit task</h4>
  <Suspense>
    <AsyncEditTask />
    <template #fallback>
      <span>
        <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
      </span>
    </template>
  </Suspense>
</template>