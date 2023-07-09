<script setup>
import RequestStatus from "../components/RequestStatus.vue";
import {useRoute} from "vue-router";
import {useProjectStore} from "../stores/project";
import FormError from "./FormError.vue";
import Datetime from "./Datetime.vue";
import {useProjectRequestsStore} from "../stores/projectRequests";

const route = useRoute();
const id = route.params.id;
const projectStore = useProjectStore()
const requestsStore = useProjectRequestsStore();

await requestsStore.load(id);
await projectStore.load(id);
const project = projectStore.project(id);
const requests = requestsStore.getRequests(id);

async function onConfirm(requestId) {
  await requestsStore.confirm(id, requestId);
}

async function onReject(requestId) {
  await requestsStore.reject(id, requestId);
}
</script>

<template>
  <FormError :error="requestsStore.error(id)" />
  <table class="table">
    <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Status</th>
      <th scope="col">Change Date</th>
      <th scope="col">User</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(request, key, index) in requests">
      <th scope="row">{{ index + 1 }}</th>
      <td>
        <span v-if="requestsStore.isLocked(request.id)"><div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...</span>
        <div v-else class="dropdown">
          <RequestStatus :status="request.status" :class="{'dropdown-toggle': request.status === 0 && project.status === 1}" data-bs-toggle="dropdown" aria-expanded="false" />
          <ul class="dropdown-menu" v-if="request.status === 0 && project.status === 1">
            <li><a class="dropdown-item" href="#" @click.prevent="onConfirm(request.id)">Confirm</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="onReject(request.id)">Reject</a></li>
          </ul>
        </div>
      </td>
      <td><Datetime :value="request.changeDate" with-time /></td>
      <td>{{ request.userFirstname }} {{ request.userLastname }} ({{ request.userEmail }})</td>
    </tr>
    </tbody>
  </table>
</template>

<style scoped>
.dropdown-toggle{
  cursor:pointer;
}
</style>