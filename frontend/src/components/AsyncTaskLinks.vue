<script setup>
import LockableButton from "../components/LockableButton.vue";
import FormError from "../components/FormError.vue";
import {useProjectStore} from "../stores/project";
import {useRoute} from "vue-router";
import TaskStatus from "./TaskStatus.vue";
import {useTaskStore} from "../stores/task";
import {useUserStore} from "../stores/user";
import Pagination from "./Pagination.vue";
import {useTaskLinksStore} from "../stores/taskLinks";
import {ref} from "vue";

const route = useRoute();
const id = route.params.id;
const taskId = route.params.taskId;
const projectStore = useProjectStore();
const taskStore = useTaskStore();
const userStore = useUserStore();
const linksStore = useTaskLinksStore();
const linkedTaskId = ref();
const linkAdditionLocked = ref(false);

await projectStore.load(id);
await taskStore.load(taskId);
await linksStore.load(id, taskId);
const project = projectStore.project(id);
const task = taskStore.task(taskId);
const user = userStore.user;
const isTaskEditor = project.isOwner || user.id === task.ownerId;

async function addLink() {
  linkAdditionLocked.value = true;
  await linksStore.create(taskId, linkedTaskId.value)
      .then((response) => {
        linkedTaskId.value = null;

        return response;
      })
      .finally(() => {
        linkAdditionLocked.value = false;
      });
}

async function removeLink(linkedTaskId) {
  await linksStore.remove(id, taskId, linkedTaskId);
}
</script>

<template>
  <FormError :error="linksStore.error(taskId)" />
  <div class="row" v-if="project.status !== 0 && task.status !== 0 && isTaskEditor">
    <div class="col-md-auto">
      <select class="form-select" v-model="linkedTaskId" :disabled="linkAdditionLocked">
        <option :value="task.id" v-for="task in linksStore.getAvailableTasks(taskId)">{{ task.name }}</option>
      </select>
    </div>
    <div class="col-auto">
      <LockableButton type="button" class="btn btn-primary ml4" @click.prevent="addLink" :locked="linkAdditionLocked" :disabled="!linkedTaskId">Add link</LockableButton>
    </div>
  </div>
  <table class="table mt-4" :class="{'loading-content': linksStore.isLoading(taskId)}">
    <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Name</th>
      <th scope="col">Status</th>
      <th scope="col"></th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="(link, key, index) in linksStore.getLinks(taskId)">
      <th scope="row">{{ index + 1 }}</th>
      <td><RouterLink :to="{name: 'edit_task', params: { id: id, taskId: link.linkedTaskId }}">{{ link.linkedTaskName }}</RouterLink></td>
      <td>
        <TaskStatus :status="link.linkedTaskStatus" />
      </td>
      <td>
        <span v-if="linksStore.isLocked(link.linkedTaskId)"><div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...</span>
        <span v-else>
              <a href="#" @click.prevent="removeLink(link.linkedTaskId)" v-if="project.status !== 0 && task.status !== 0 && isTaskEditor">Remove</a>
            </span>
      </td>
    </tr>
    </tbody>
  </table>
  <Pagination :metadata="linksStore.getPaginationMetadata(taskId)" :locked="linksStore.isLoading(taskId)" />
</template>