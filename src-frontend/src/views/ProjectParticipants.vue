<script setup>
import AsyncProjectParticipantsList from "../components/AsyncProjectParticipantsList.vue";
</script>

<template>
  <h4 class="my-4">Project participants</h4>
  <Suspense>
    <AsyncProjectParticipantsList />
    <template #fallback>
        <span>
          <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
        </span>
    </template>
  </Suspense>
</template>