<script setup>
import axiosInstance from "../helpers/axios";
import {reactive} from "vue";
import ProjectStatus from "./ProjectStatus.vue";

/**
 * @type {{
 * id: string,
 * name: string,
 * finisDate: datetime,
 * ownerEmail: string,
 * ownerFirstname: string,
 * ownerLastname: string,
 * status: number,
 * tasksCount: number,
 * participantsCount: number,
 * isOwner: boolean,
 * lastRequestStatus: number
 * }[]}
 */
const projects = reactive([]);

await axiosInstance.get('/projects/').then((response) => {
  for (const [key, value] of Object.entries(response.data)) {
    projects.push(value);
  }
  return response;
});
</script>

<template>
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
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(project, num) in projects">
        <th scope="row">{{ num + 1 }}</th>
        <td>
          <RouterLink :to="{name: 'edit_project', params: { id: project.id }}" v-if="project.isOwner">{{ project.name }}</RouterLink>
          <span v-else>{{ project.name }}</span>
        </td>
        <td>{{ project.finishDate }}</td>
        <td>{{ project.ownerFirstname }} {{ project.ownerLastname }} ({{ project.ownerEmail }})</td>
        <td><ProjectStatus :status="project.status" /></td>
        <td>{{ project.tasksCount }}</td>
        <td>{{ project.participantsCount }}</td>
        <td></td>
      </tr>
    </tbody>
  </table>
</template>