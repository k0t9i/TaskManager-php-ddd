<script setup>
import RequestStatus from "../components/RequestStatus.vue";
import {reactive} from "vue";
import axiosInstance from "../helpers/axios";
import Datetime from "./Datetime.vue";
import {useQueryStore} from "../stores/queryStore";

/**
 * @type {{
 * status: number,
 * changeDate: number,
 * projectName: string
 * }[]}
 */
const requests = reactive([]);
const queryStore = useQueryStore();

await axiosInstance
    .get('/users/requests/', {
      params: queryStore.getParams
    })
    .then((response) => {
      for (const [key, value] of Object.entries(response.data.items)) {
        let val = value;
        val.changeDate = new Date(value.changeDate);
        requests.push(val);
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
      <td><Datetime :value="request.changeDate" with-time /></td>
      <td>{{ request.projectName }}</td>
    </tr>
    </tbody>
  </table>
</template>