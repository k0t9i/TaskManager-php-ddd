<script setup>
import {useRoute} from "vue-router";
import {useProjectStore} from "../stores/project";
import {useProjectRequestsStore} from "../stores/projectRequests";

const route = useRoute();
const projectStore = useProjectStore();
const requestsStore = useProjectRequestsStore();
const id = route.params.id;

await projectStore.load(id);
await requestsStore.load(id);
const project = projectStore.project(id);
</script>

<template>
  <h3 class="my-4">Project "{{ project.name }}"</h3>
  <div class="container-md">
    <div class="row mt-4">
      <div class="col">
        <ul class="nav flex-column">
          <li class="nav-item">
            <RouterLink :to="{name: 'project_info'}" class="nav-link">Project info</RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink :to="{name: 'project_requests'}" class="nav-link" v-if="project.isOwner">Requests ({{ requestsStore.countPending(id) }}/{{ requestsStore.countAll(id) }})</RouterLink>
          </li>
        </ul>
      </div>
      <div class="col-md-9">
        <RouterView />
      </div>
    </div>
  </div>
</template>
