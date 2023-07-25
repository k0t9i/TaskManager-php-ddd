<script setup>
import {useTaskStore} from "../stores/task";
import {useRoute} from "vue-router";

const route = useRoute();
const taskStore = useTaskStore();
const taskId = route.params.taskId;
const task = taskStore.task(taskId);
</script>

<template>
  <h4 class="my-4">Task "{{ task.name }}"</h4>
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item" role="presentation">
      <RouterLink class="nav-link" :to="{name: 'task_info', params: {taskId: taskId}}">Task</RouterLink>
    </li>
    <li class="nav-item" role="presentation">
      <RouterLink class="nav-link" :to="{name: 'task_links', params: {taskId: taskId}}">Links</RouterLink>
    </li>
  </ul>
  <div class="tab-content mt-4">
    <Suspense>
      <RouterView :key="route.path" />
      <template #fallback>
        <span>
          <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
        </span>
      </template>
    </Suspense>
  </div>
</template>