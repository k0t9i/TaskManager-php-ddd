<script setup>
import {useRoute} from "vue-router";
import {useProjectStore} from "../stores/project";
import FormError from "./FormError.vue";
import {useProjectParticipantsStore} from "../stores/projectParticipants";
import confirmModal from "./confirmModal";
import Pagination from "./Pagination.vue";

const route = useRoute();
const id = route.params.id;
const projectStore = useProjectStore()
const participantsStore = useProjectParticipantsStore();

await participantsStore.load(id);
await projectStore.load(id);
const project = projectStore.project(id);

async function onRemove(participantId) {
  await participantsStore.remove(id, participantId);
}

async function makeOwner(participantId) {
  await projectStore.makeOwner(id, participantId);
}
</script>

<template>
  <FormError :error="participantsStore.error(id)" />
  <table class="table" :class="{'loading-content': participantsStore.isLoading(id)}">
    <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Email</th>
      <th scope="col">Firstname</th>
      <th scope="col">Lastname</th>
      <th scope="col"></th>
      <th scope="col"></th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(participant, key, index) in participantsStore.getParticipants(id)">
      <th scope="row">{{ index + 1 }}</th>
      <td>{{ participant.userEmail }}</td>
      <td>{{ participant.userFirstname }}</td>
      <td>{{ participant.userLastname }}</td>
      <td>
        <div v-if="project.status !== 0 && project.isOwner && participant.tasksCount === 0">
          <span v-if="participantsStore.isLocked(participant.userId)"><div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...</span>
          <a href="#" v-else @click.prevent="confirmModal.show(() => {
            onRemove(participant.userId);
          }, `Remove ${participant.userFirstname} ${participant.userLastname} from the project?`)">Remove</a>
        </div>
      </td>
      <td>
        <div v-if="project.status !== 0 && project.isOwner">
          <span v-if="projectStore.isLocked(id)"><div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...</span>
          <a href="#" v-else @click.prevent="confirmModal.show(() => {
            makeOwner(participant.userId);
          }, `Make ${participant.userFirstname} ${participant.userLastname} the owner of the project?`)">Make the owner</a>
        </div>
      </td>
    </tr>
    </tbody>
  </table>
  <Pagination :metadata="participantsStore.getPaginationMetadata(id)" :locked="participantsStore.isLoading(id)" />
</template>