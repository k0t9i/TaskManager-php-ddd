<script setup>
import {useQueryStore} from "../stores/queryStore";

const queryStore = useQueryStore();
</script>

<template>
  <h4 class="my-4">Project tasks</h4>
  <Suspense>
    <RouterView :key="queryStore.getParams" />
    <template #fallback>
        <span>
          <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
        </span>
    </template>
  </Suspense>
</template>