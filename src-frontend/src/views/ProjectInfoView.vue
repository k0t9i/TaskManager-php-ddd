<script setup>

</script>

<template>
  <div class="container-md">
    <h3 class="my-4">Project info</h3>
    <div class="row mt-4">
      <div class="col">
        <ul class="nav flex-column">
          <li class="nav-item">
            <RouterLink :to="{name: 'edit_project'}" class="nav-link">Edit project</RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink :to="{name: 'project_requests'}" class="nav-link">Requests</RouterLink>
          </li>
        </ul>
      </div>
      <div class="col-md-9">
        <RouterView />
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>