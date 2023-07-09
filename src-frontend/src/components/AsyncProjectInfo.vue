<script setup>
import FormSuccess from "../components/FormSuccess.vue";
import LockableButton from "../components/LockableButton.vue";
import FormError from "../components/FormError.vue";
import {ref} from "vue";
import Datepicker from 'vue3-datepicker';
import ProjectStatus from "../components/ProjectStatus.vue";
import {useProjectStore} from "../stores/project";
import {useRoute} from "vue-router";

const route = useRoute();
const id = route.params.id;
const success = ref(false);
const projectStore = useProjectStore();

await projectStore.load(id);
const project = projectStore.project(id);

async function onSubmit() {
  success.value = false;
  await projectStore.save(id)
      .then((result) => {
        success.value = true;
        return result;
      })
}

async function toggleStatus() {
  await projectStore.toggleStatus(id);
}
</script>

<template>
  <form @submit.prevent="onSubmit">
    <fieldset :disabled="projectStore.isLocked(id) || project.status === 0 || !project.isOwner">
      <FormError :error="projectStore.error(id)" />
      <FormSuccess v-if="success">Successfully saved.</FormSuccess>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <div class="h5">
          <span v-if="projectStore.isLocked(id)" class="badge bg-light text-dark">
            <div class="spinner-border spinner-border-sm mx-1" role="status" />Loading...
          </span>
          <span v-else>
            <a href="#" v-if="project.isOwner" @click.prevent="toggleStatus"><ProjectStatus :status="project.status" /></a>
            <ProjectStatus v-else :status="project.status" />
          </span>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" required="required" class="form-control" v-model="project.name">
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="10" v-model="project.description"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Finish Date</label>
        <Datepicker class="form-control" v-model="project.finishDate" />
      </div>
      <div class="mb-3 text-end" v-if="project.isOwner">
        <LockableButton type="submit" class="btn btn-primary" :locked="projectStore.isLocked(id)">Save</LockableButton>
      </div>
    </fieldset>
  </form>
</template>