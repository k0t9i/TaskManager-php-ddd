<script setup>
import RequestStatus from "../components/RequestStatus.vue";
import Datetime from "./Datetime.vue";
import FormError from "./FormError.vue";
import {useUserRequestsStore} from "../stores/userRequests";
import Pagination from "./Pagination.vue";

const userRequestsStore = useUserRequestsStore();
await userRequestsStore.load();

/**
 * @type {{
 * status: number,
 * changeDate: number,
 * projectName: string
 * }[]}
 */
const requests = userRequestsStore.requests;
</script>

<template>
  <FormError :error="userRequestsStore.error" />
  <table class="table" :class="{'loading-content': userRequestsStore.isLoading}">
    <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Status</th>
      <th scope="col">Change Date</th>
      <th scope="col">Project</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(request, key) in requests">
      <th scope="row">{{ key + 1 }}</th>
      <td><RequestStatus :status="request.status" /></td>
      <td><Datetime :value="request.changeDate" with-time /></td>
      <td>{{ request.projectName }}</td>
    </tr>
    </tbody>
  </table>
  <Pagination :metadata="userRequestsStore.getPaginationMetadata" :locked="userRequestsStore.isLoading" />
</template>