<script setup>
import AsyncUserRequestList from "../components/AsyncUserRequestList.vue";
</script>

<template>
  <div class="container-md">
    <h3 class="mt-4">Requests</h3>
    <Suspense>
      <AsyncUserRequestList />
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