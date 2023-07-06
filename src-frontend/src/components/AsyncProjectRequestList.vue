<script setup>
import RequestStatus from "../components/RequestStatus.vue";
import {reactive} from "vue";
import axiosInstance from "../helpers/axios";
import {useRoute} from "vue-router";

const route = useRoute();

/**
 * @type {{
 * status: number,
 * changeDate: number,
 * userEmail: string,
 * userFirstname: string,
 * userLastname: string
 * }[]}
 */
const requests = reactive([]);

await axiosInstance.get(`/projects/${route.params.id}/requests/`)
    .then((response) => {
      for (const [key, value] of Object.entries(response.data)) {
        requests.push(value);
      }
      return response;
    });
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
    <tr v-for="(request, key) in requests">
      <th scope="row">{{ key + 1 }}</th>
      <td><RequestStatus :status="request.status" /></td>
      <td>{{ request.changeDate }}</td>
      <td>{{ request.userFirstname }} {{ request.userLastname }} ({{ request.userEmail }})</td>
    </tr>
    </tbody>
  </table>
</template>