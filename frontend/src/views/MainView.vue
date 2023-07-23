<script setup>
import {useRoute} from "vue-router";
import {useAvailableProjectsStore} from "../stores/availableProjects";
import Pagination from "../components/Pagination.vue";

const route = useRoute();
const projectsStore = useAvailableProjectsStore();
</script>

<template>
  <div class="container-md">
    <h3 class="my-4">Available projects</h3>
    <RouterLink :to="{name: 'create_project'}" custom v-slot="{navigate}">
      <button @click="navigate" class="btn btn-primary mb-4">Create project</button>
    </RouterLink>
    <div>
      <Suspense>
        <RouterView :key="route.fullPath" />
        <template #fallback>
        <span>
          <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
        </span>
        </template>
      </Suspense>
      <Pagination :metadata="projectsStore.getPaginationMetadata" :locked="projectsStore.isLoading" />
    </div>
  </div>
</template>

<style scoped>

</style>