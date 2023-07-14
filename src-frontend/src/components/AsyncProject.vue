<script setup>
import {useRoute} from "vue-router";
import {useProjectStore} from "../stores/project";
import {useProjectRequestsStore} from "../stores/projectRequests";
import {useTasksStore} from "../stores/tasks";
import {ref} from "vue";
import axiosInstance from "../helpers/axios";
import LockableButton from "./LockableButton.vue";
import router from "../router";
import {useProjectParticipantsStore} from "../stores/projectParticipants";

const route = useRoute();
const projectStore = useProjectStore();
const requestsStore = useProjectRequestsStore();
const tasksStore = useTasksStore();
const participantsStore = useProjectParticipantsStore();
const id = route.params.id;
const isLeaveLocked = ref(false);
const error = ref('');

await projectStore.load(id);
const project = projectStore.project(id);
if (project.isOwner) {
  await requestsStore.load(id);
}
await tasksStore.load(id);
await participantsStore.load(id);

async function onLeave(id) {
  error.value = '';
  isLeaveLocked.value = true;
  await axiosInstance.patch(`/projects/${id}/leave/`)
      .then((response) => {
        return router.push({name: 'user_projects'});
      })
      .catch((e) => {
        error.value = e.response.data.message;
      })
      .finally(() => {
        isLeaveLocked.value = false;
      });
}
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
          <li class="nav-item">
            <RouterLink :to="{name: 'project_tasks'}" class="nav-link">Tasks ({{ tasksStore.countAll(id) }})</RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink :to="{name: 'project_participants'}" class="nav-link">Participants ({{ participantsStore.countAll(id) }})</RouterLink>
          </li>
        </ul>
        <LockableButton
            @click.prevent="onLeave(project.id)"
            v-if="project.id && !project.isOwner && project.status !== 0 && tasksStore.countAll(project.id) === 0"
            class="btn btn-outline-danger btn-sm m-3"
            :locked="isLeaveLocked"
        >
          Leave the project
        </LockableButton>
      </div>
      <div class="col-md-9">
        <RouterView />
      </div>
    </div>
  </div>
</template>
