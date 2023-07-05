<script setup>
import {useRoute} from 'vue-router';
import AsyncProjectForm from "../components/AsyncProjectForm.vue";
import ProjectStatus from "../components/ProjectStatus.vue";

const route = useRoute();
</script>

<template>
  <div class="container-md">
    <h3 class="mt-4">Edit project</h3>
    <Suspense>
      <AsyncProjectForm :id="route.params.id" />
      <template #fallback>
        <span>
          <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
        </span>
      </template>
    </Suspense>
  </div>
</template>