<script setup>
import FormError from "../components/FormError.vue";
import FormSuccess from "../components/FormSuccess.vue";
import Datepicker from "vue3-datepicker";
import LockableButton from "../components/LockableButton.vue";
import {reactive, ref} from "vue";
import {useTasksStore} from "../stores/tasks";
import {useRoute} from "vue-router";
import {useProjectStore} from "../stores/project";
import CommonTaskFormFields from "./CommonTaskFormFields.vue";

const route = useRoute();
const id = route.params.id;
const projectStore = useProjectStore();
const tasksStore = useTasksStore();
const isLocked = ref(false);
const taskId = ref();
const task = reactive({
  name: '',
  brief: '',
  description: '',
  startDate: null,
  finishDate: null
});

await projectStore.load(id);
await tasksStore.load(id);

const project = projectStore.project(id);

async function onSubmit() {
  isLocked.value = true;
  taskId.value = null;

  await tasksStore.create(id, task)
      .then((response) => {
        if (!tasksStore.error(id)) {
          taskId.value = task.id;
          task.name = '';
          task.brief = '';
          task.description = '';
          task.startDate = null;
          task.finishDate = null;
        }

        return response;
      })
      .finally(() => {
        isLocked.value = false;
      });
}
</script>

<template>
  <div class="container-md">
    <form @submit.prevent="onSubmit">
      <fieldset class="row mt-4" :disabled="isLocked">
        <FormError :error="tasksStore.error(id)" />
        <FormSuccess v-if="taskId">
          Successfully saved. <RouterLink :to="{name: 'edit_task', params: { id: id, taskId: taskId }}">Edit</RouterLink> this task.
        </FormSuccess>
        <CommonTaskFormFields :project="project" :task="task" />
        <div class="mb-3 text-end">
          <LockableButton type="submit" class="btn btn-primary" :locked="isLocked">Save</LockableButton>
        </div>
      </fieldset>
    </form>
  </div>
</template>
