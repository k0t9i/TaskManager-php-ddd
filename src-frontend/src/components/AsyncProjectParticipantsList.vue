<script setup>
import {useRoute} from "vue-router";
import {useProjectStore} from "../stores/project";
import FormError from "./FormError.vue";
import {useProjectParticipantsStore} from "../stores/projectParticipants";

const route = useRoute();
const id = route.params.id;
const projectStore = useProjectStore()
const participantsStore = useProjectParticipantsStore();

await participantsStore.load(id);
await projectStore.load(id);
const project = projectStore.project(id);
const participants = participantsStore.getParticipants(id);

async function onRemove(participantId) {
  await participantsStore.remove(id, participantId);
}

async function makeOwner(participantId) {
  await projectStore.makeOwner(id, participantId);
}
</script>

<template>
  <FormError :error="participantsStore.error(id)" />
  <table class="table">
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
    <tr v-for="(participant, key, index) in participants">
      <th scope="row">{{ index + 1 }}</th>
      <td>{{ participant.userEmail }}</td>
      <td>{{ participant.userFirstname }}</td>
      <td>{{ participant.userLastname }}</td>
      <td>
        <div v-if="project.status !== 0 && project.isOwner && participant.tasksCount === 0">
          <span v-if="participantsStore.isLocked(participant.userId)"><div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...</span>
          <a href="#" v-else @click.prevent="onRemove(participant.userId)">Remove</a>
        </div>
      </td>
      <td>
        <div v-if="project.status !== 0 && project.isOwner">
          <span v-if="projectStore.isLocked(id)"><div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...</span>
          <a href="#" v-else @click.prevent="makeOwner(participant.userId)">Make the owner</a>
        </div>
      </td>
    </tr>
    </tbody>
  </table>
</template>