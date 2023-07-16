<script setup>
import ProjectStatus from "./ProjectStatus.vue";
import RequestStatus from "./RequestStatus.vue";
import Datetime from "./Datetime.vue";
import FormError from "./FormError.vue";
import {useAvailableProjectsStore} from "../stores/availableProjects";
import confirmModal from "./confirmModal";

const projectsStore = useAvailableProjectsStore();
await projectsStore.load();
/**
 * @type {Object<{
 * id: string,
 * name: string,
 * finishDate: Date,
 * ownerFullName: string,
 * status: number,
 * tasksCount: number,
 * participantsCount: number,
 * isOwner: boolean,
 * isInvolved: boolean,
 * lastRequestStatus: number
 * }>}
 */
const projects = projectsStore.projects;

async function onJoin(projectId) {
  await projectsStore.join(projectId);
}
</script>

<template>
  <FormError :error="projectsStore.error" />
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
          <RouterLink :to="{name: 'project', params: { id: project.id }}" v-if="project.isInvolved">{{ project.name }}</RouterLink>
          <span v-else>{{ project.name }}</span>
        </td>
        <td><Datetime :value="project.finishDate" /></td>
        <td>{{ project.ownerFullName }}</td>
        <td><ProjectStatus :status="project.status" /></td>
        <td>{{ project.tasksCount }}</td>
        <td>{{ project.participantsCount }}</td>
        <td><RequestStatus :status="project.lastRequestStatus" /></td>
        <td>
          <span v-if="projectsStore.isLocked(project.id)"><div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...</span>
          <span v-else>
            <a href="#" @click.prevent='confirmModal.show(() => {
              onJoin(project.id);
            }, `Join the project "${project.name}"?`)' v-if="!project.isInvolved && ![0, 1].includes(project.lastRequestStatus) && project.status === 1">Join</a>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</template>