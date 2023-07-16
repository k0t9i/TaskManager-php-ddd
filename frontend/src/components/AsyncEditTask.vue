<script setup>
import FormSuccess from "../components/FormSuccess.vue";
import LockableButton from "../components/LockableButton.vue";
import FormError from "../components/FormError.vue";
import {ref} from "vue";
import {useProjectStore} from "../stores/project";
import {useRoute} from "vue-router";
import TaskStatus from "./TaskStatus.vue";
import {useTaskStore} from "../stores/task";
import CommonTaskFormFields from "./CommonTaskFormFields.vue";
import {useUserStore} from "../stores/user";
import {useTaskLinksStore} from "../stores/taskLinks";

const route = useRoute();
const id = route.params.id;
const taskId = route.params.taskId;
const success = ref(false);
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
  <h4 class="my-4">Task "{{ task.name }}"</h4>
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#task" type="button">Task</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#links" type="button">Links</button>
    </li>
  </ul>
  <div class="tab-content mt-4">
    <div class="tab-pane fade show active" id="task" role="tabpanel">
      <form @submit.prevent="onSubmit">
        <fieldset :disabled="taskStore.isLocked(taskId) || project.status === 0 || task.status === 0 || !isTaskEditor">
          <FormError :error="taskStore.error(taskId)" />
          <FormSuccess v-if="success">Successfully saved.</FormSuccess>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <div class="h5">
              <span v-if="taskStore.isLocked(taskId)" class="badge bg-light text-dark">
                <div class="spinner-border spinner-border-sm mx-1" role="status" />Loading...
              </span>
              <span v-else>
                <a href="#" v-if="project.status !== 0 && isTaskEditor" @click.prevent="toggleStatus"><TaskStatus :status="task.status" /></a>
                <TaskStatus v-else :status="task.status" />
              </span>
            </div>
          </div>
          <CommonTaskFormFields :project="project" :task="task" />
          <div class="mb-3 text-end" v-if="isTaskEditor">
            <LockableButton type="submit" class="btn btn-primary" :locked="taskStore.isLocked(taskId)">Save</LockableButton>
          </div>
        </fieldset>
      </form>
    </div>
    <div class="tab-pane fade" id="links" role="tabpanel">
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
      <table class="table mt-4">
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
    </div>
  </div>
</template>