<script setup>
import AsyncProjectTaskList from "../components/AsyncProjectTaskList.vue";
</script>

<template>
  <h4 class="my-4">Project tasks</h4>
  <Suspense>
    <AsyncProjectTaskList />
    <template #fallback>
        <span>
          <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
        </span>
    </template>
  </Suspense>
</template>