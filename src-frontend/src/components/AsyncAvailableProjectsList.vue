<script setup>
import axiosInstance from "../helpers/axios";
import {reactive, ref} from "vue";
import ProjectStatus from "./ProjectStatus.vue";
import RequestStatus from "./RequestStatus.vue";
import Datetime from "./Datetime.vue";
import FormError from "./FormError.vue";

/**
 * @type {Object<{
 * id: string,
 * name: string,
 * finishDate: Date,
 * ownerEmail: string,
 * ownerFirstname: string,
 * ownerLastname: string,
 * status: number,
 * tasksCount: number,
 * participantsCount: number,
 * isOwner: boolean,
 * isParticipating: boolean,
 * lastRequestStatus: number
 * }>}
 */
const projects = reactive({});
const isJoinLocked = ref({});
const error = ref('');

await axiosInstance.get('/projects/')
    .then((response) => {
      for (const [key, value] of Object.entries(response.data)) {
        projects[value.id] = value;
        projects[value.id].finishDate = new Date(value.finishDate);
      }
      return response;
    });

async function onJoin(projectId) {
  error.value = '';
  isJoinLocked.value[projectId] = true;
  await axiosInstance.post(`/projects/${projectId}/requests/`)
      .then((response) => {
        projects[projectId].lastRequestStatus = 0;
        return response;
      })
      .catch((e) => {
        error.value = e.response.data.message;
      })
      .finally(() => {
        isJoinLocked.value[projectId] = false;
      });
}
</script>

<template>
  <FormError :error="error" />
  <table class="table">
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">Name</th>
        <th scope="col">Finish Date</th>
        <th scope="col">Owner</th>
        <th scope="col">Status</th>
        <th scope="col">Tasks count</th>
        <th scope="col">Participants count</th>
        <th scope="col">Last request status</th>
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(project, key, index) in projects">
        <th scope="row">{{ index + 1 }}</th>
        <td>
          <RouterLink :to="{name: 'project', params: { id: project.id }}" v-if="project.isOwner || project.isParticipating">{{ project.name }}</RouterLink>
          <span v-else>{{ project.name }}</span>
        </td>
        <td><Datetime :value="project.finishDate" /></td>
        <td>{{ project.ownerFirstname }} {{ project.ownerLastname }} ({{ project.ownerEmail }})</td>
        <td><ProjectStatus :status="project.status" /></td>
        <td>{{ project.tasksCount }}</td>
        <td>{{ project.participantsCount }}</td>
        <td><RequestStatus :status="project.lastRequestStatus" /></td>
        <td>
          <span v-if="isJoinLocked[project.id]"><div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...</span>
          <span v-else>
            <a href="#" @click.prevent="onJoin(project.id)" v-if="!project.isOwner && ![0, 1].includes(project.lastRequestStatus) && project.status === 1">Join</a>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</template>