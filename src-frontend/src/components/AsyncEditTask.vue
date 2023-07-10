<script setup>
import FormSuccess from "../components/FormSuccess.vue";
import LockableButton from "../components/LockableButton.vue";
import FormError from "../components/FormError.vue";
import {ref} from "vue";
import Datepicker from 'vue3-datepicker';
import {useProjectStore} from "../stores/project";
import {useRoute} from "vue-router";
import {useTasksStore} from "../stores/tasks";
import TaskStatus from "./TaskStatus.vue";
import {useTaskStore} from "../stores/task";
import CommonTaskFormFields from "./CommonTaskFormFields.vue";

const route = useRoute();
const id = route.params.id;
const taskId = route.params.taskId;
const success = ref(false);
const projectStore = useProjectStore();
const tasksStore = useTasksStore();
const taskStore = useTaskStore();

await projectStore.load(id);
await tasksStore.load(id);
await taskStore.load(taskId);
const project = projectStore.project(id);
const task = taskStore.task(taskId);

async function onSubmit() {
  success.value = false;
  await taskStore.update(taskId)
      .then((result) => {
        if (!taskStore.error(taskId)) {
          success.value = true;
        }
        return result;
      });
}

async function toggleStatus() {
  await taskStore.toggleStatus(taskId);
}
</script>

<template>
  <form @submit.prevent="onSubmit">
    <fieldset :disabled="taskStore.isLocked(taskId) || project.status === 0 || task.status === 0">
      <FormError :error="taskStore.error(taskId)" />
      <FormSuccess v-if="success">Successfully saved.</FormSuccess>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <div class="h5">
          <span v-if="taskStore.isLocked(taskId)" class="badge bg-light text-dark">
            <div class="spinner-border spinner-border-sm mx-1" role="status" />Loading...
          </span>
          <span v-else>
            <a href="#" v-if="project.status !== 0" @click.prevent="toggleStatus"><TaskStatus :status="task.status" /></a>
            <TaskStatus v-else :status="task.status" />
          </span>
        </div>
      </div>
      <CommonTaskFormFields :project="project" :task="task" />
      <div class="mb-3 text-end">
        <LockableButton type="submit" class="btn btn-primary" :locked="taskStore.isLocked(taskId)">Save</LockableButton>
      </div>
    </fieldset>
  </form>
</template>