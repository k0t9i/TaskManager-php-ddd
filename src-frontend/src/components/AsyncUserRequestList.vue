<script setup>
import RequestStatus from "../components/RequestStatus.vue";
import {reactive} from "vue";
import axiosInstance from "../helpers/axios";

/**
 * @type {{
 * status: number,
 * changeDate: number,
 * projectName: string
 * }[]}
 */
const requests = reactive([]);

await axiosInstance.get('/users/requests/')
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
      <th scope="col">Project</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(request, key) in requests">
      <th scope="row">{{ key + 1 }}</th>
      <td><RequestStatus :status="request.status" /></td>
      <td>{{ request.changeDate }}</td>
      <td>{{ request.projectName }}</td>
    </tr>
    </tbody>
  </table>
</template>