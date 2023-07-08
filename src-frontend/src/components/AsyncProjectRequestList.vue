<script setup>
import RequestStatus from "../components/RequestStatus.vue";
import {reactive, ref} from "vue";
import axiosInstance from "../helpers/axios";
import {useRoute} from "vue-router";

const route = useRoute();

/**
 * @type {Object<{
 * id: string,
 * status: number,
 * changeDate: number,
 * userEmail: string,
 * userFirstname: string,
 * userLastname: string
 * }>}
 */
const requests = reactive({});
const isLocked = ref({});

await axiosInstance.get(`/projects/${route.params.id}/requests/`)
    .then((response) => {
      for (const [key, value] of Object.entries(response.data)) {
        requests[value.id] = value;
      }
      return response;
    });

async function onConfirm(requestId) {
  await changeStatus(requestId, 'confirm', 1);
}

async function onReject(requestId) {
  await changeStatus(requestId, 'reject', 2);
}

async function changeStatus(requestId, endpoint, targetStatus) {
  isLocked.value[requestId] = true;
  await axiosInstance.patch(`/projects/${route.params.id}/requests/${requestId}/${endpoint}/`)
      .then((response) => {
        requests[requestId].status = targetStatus;
        isLocked.value[requestId] = false;
        return response;
      });
}
</script>

<template>
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
          <RequestStatus :status="request.status" :class="{'dropdown-toggle': request.status === 0}" data-bs-toggle="dropdown" aria-expanded="false" />
          <ul class="dropdown-menu">
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