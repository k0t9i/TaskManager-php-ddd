<script setup>
import AsyncProjectForm from "../components/AsyncProjectForm.vue";
</script>

<template>
  <h4 class="mt-4">Edit project</h4>
  <Suspense>
    <AsyncProjectForm />
    <template #fallback>
      <span>
        <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
      </span>
    </template>
  </Suspense>
</template>