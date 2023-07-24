<script setup>
import {useQueryStore} from "../stores/queryStore";

const queryStore = useQueryStore();
</script>

<template>
  <div class="container-md">
    <h3 class="my-4">My requests</h3>
    <Suspense>
      <RouterView :key="queryStore.getParams" />
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