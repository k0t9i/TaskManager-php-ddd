<script setup>
import AsyncUserProjectList from "../components/AsyncUserProjectList.vue";
</script>

<template>
  <div class="container-md">
    <h3 class="my-4">My projects</h3>
    <RouterLink :to="{name: 'create_project'}" custom v-slot="{navigate}">
      <button @click="navigate" class="btn btn-primary mb-4">Create project</button>
    </RouterLink>
    <div>
      <Suspense>
        <AsyncUserProjectList />
        <template #fallback>
        <span>
          <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
        </span>
        </template>
      </Suspense>
    </div>
  </div>
</template>

<style scoped>

</style>