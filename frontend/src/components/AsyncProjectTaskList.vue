<script setup>
import {useRoute} from "vue-router";
import {useProjectStore} from "../stores/project";
import FormError from "./FormError.vue";
import Datetime from "./Datetime.vue";
import {useTasksStore} from "../stores/tasks";
import TaskStatus from "./TaskStatus.vue";

const route = useRoute();
const id = route.params.id;
const projectStore = useProjectStore()
const tasksStore = useTasksStore();

await tasksStore.load(id);
await projectStore.load(id);
const project = projectStore.project(id);
const tasks = tasksStore.getTasks(id);
</script>

<template>
  <RouterLink :to="{name: 'create_task'}" custom v-slot="{navigate}" v-if="project.status !== 0">
    <button @click="navigate" class="btn btn-primary mb-4">Create task</button>
  </RouterLink>
  <FormError :error="tasksStore.error(id)" />
  <table class="table">
    <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Name</th>
      <th scope="col">Status</th>
      <th scope="col">Start Date</th>
      <th scope="col">Finish Date</th>
      <th scope="col">Owner</th>
      <th scope="col">Links count</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(task, key, index) in tasks">
      <th scope="row">{{ index + 1 }}</th>
      <td>
        <RouterLink :to="{name: 'edit_task', params: { id: project.id, taskId: task.id }}">{{ task.name }}</RouterLink>
      </td>
      <td><TaskStatus :status="task.status" /></td>
      <td><Datetime :value="task.startDate" /></td>
      <td><Datetime :value="task.finishDate" /></td>
      <td>{{ task.ownerFullName }}</td>
      <td>{{ task.linksCount }}</td>
    </tr>
    </tbody>
  </table>
</template>