<script setup>
import AsyncProjectRequestList from "../components/AsyncProjectRequestList.vue";
</script>

<template>
  <h4 class="my-4">Project requests</h4>
  <Suspense>
    <AsyncProjectRequestList />
    <template #fallback>
        <span>
          <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
        </span>
    </template>
  </Suspense>
</template>