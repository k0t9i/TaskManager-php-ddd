<script setup>
import {reactive} from "vue";
import axiosInstance from "../helpers/axios";
import ProjectStatus from "./ProjectStatus.vue";
import Datetime from "./Datetime.vue";

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
 * participantsCount: number
 * }>}
 */
const projects = reactive({});

await axiosInstance.get('/users/projects/')
    .then((response) => {
      for (const [key, value] of Object.entries(response.data)) {
        projects[value.id] = value;
        projects[value.id].finishDate = new Date(value.finishDate);
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
    </tr>
    </thead>
    <tbody>
    <tr v-for="(project, key, index) in projects">
      <th scope="row">{{ index + 1 }}</th>
      <td>
        <RouterLink :to="{name: 'project', params: { id: project.id }}">{{ project.name }}</RouterLink>
      </td>
      <td><Datetime :value="project.finishDate" /></td>
      <td>{{ project.ownerFirstname }} {{ project.ownerLastname }} ({{ project.ownerEmail }})</td>
      <td><ProjectStatus :status="project.status" /></td>
      <td>{{ project.tasksCount }}</td>
      <td>{{ project.participantsCount }}</td>
    </tr>
    </tbody>
  </table>
</template>