<script setup>
import RequestStatus from "../components/RequestStatus.vue";
import {reactive, ref} from "vue";
import axiosInstance from "../helpers/axios";
import {useRoute} from "vue-router";
import {useProjectStore} from "../stores/project";
import FormError from "./FormError.vue";

const route = useRoute();
const id = route.params.id;
/**
 * @type {Object<{
 * id: string,
 * status: number,
 * changeDate: Date,
 * userEmail: string,
 * userFirstname: string,
 * userLastname: string
 * }>}
 */
const requests = reactive({});
const isLocked = ref({});
const error = ref('');
const projectStore = useProjectStore();

await axiosInstance.get(`/projects/${id}/requests/`)
    .then((response) => {
      for (const [key, value] of Object.entries(response.data)) {
        requests[value.id] = value;
        requests[value.id].changeDate = new Date(value.changeDate);
      }
      return projectStore.load(id);
    });
const project = projectStore.project(id);

async function onConfirm(requestId) {
  await changeStatus(requestId, 'confirm', 1);
}

async function onReject(requestId) {
  await changeStatus(requestId, 'reject', 2);
}

async function changeStatus(requestId, endpoint, targetStatus) {
  error.value = '';
  isLocked.value[requestId] = true;
  await axiosInstance.patch(`/projects/${id}/requests/${requestId}/${endpoint}/`)
      .then((response) => {
        requests[requestId].status = targetStatus;
        return response;
      })
      .catch((e) => {
        error.value = e.response.data.message;
      })
      .finally(() => {
        isLocked.value[requestId] = false;
      });
}
</script>

<template>
  <FormError :error="error" />
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
        <span v-if="isLocked[request.id]"><div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...</span>
        <div v-else class="dropdown">
          <RequestStatus :status="request.status" :class="{'dropdown-toggle': request.status === 0 && project.status === 1}" data-bs-toggle="dropdown" aria-expanded="false" />
          <ul class="dropdown-menu" v-if="request.status === 0 && project.status === 1">
            <li><a class="dropdown-item" href="#" @click.prevent="onConfirm(request.id)">Confirm</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="onReject(request.id)">Reject</a></li>
          </ul>
        </div>
      </td>
      <td>{{ request.changeDate }}</td>
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