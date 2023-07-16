<script setup>
import AsyncCreateTask from "../components/AsyncCreateTask.vue";
</script>

<template>
  <h4 class="my-4">Create task</h4>
  <Suspense>
    <AsyncCreateTask />
    <template #fallback>
        <span>
          <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
        </span>
    </template>
  </Suspense>
</template>