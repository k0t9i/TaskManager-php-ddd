<script setup>
import FormSuccess from "../components/FormSuccess.vue";
import LockableButton from "../components/LockableButton.vue";
import FormError from "../components/FormError.vue";
import {reactive, ref} from "vue";
import axiosInstance from "../helpers/axios";
import Datepicker from 'vue3-datepicker';
import router from "../router";
import routes from "../router/routes";

const error = ref();
const projectId = ref();
const project = reactive({
  name: '',
  description: '',
  finishDate: null
});
const isLocked = ref(false);

function onSubmit() {
  error.value = '';
  projectId.value = null;
  isLocked.value = true;

  return axiosInstance
      .post('/projects/', project)
      .then(onSuccess)
      .catch((e) => {
        error.value = e.response.data.message;
      })
      .finally(() => {
        isLocked.value = false;
      });
}

function onSuccess(response) {
  projectId.value = response.data.id;
  project.name = '';
  project.description = '';
  project.finishDate = null;

  return response;
}
</script>

<template>
  <div class="container-md">
    <h3 class="mt-4">Create project</h3>
    <form @submit.prevent="onSubmit">
      <fieldset class="row mt-4" :disabled="isLocked">
        <div class="col"></div>
        <div class="col-md-6">
          <FormError :error="error" />
          <FormSuccess v-if="projectId">
            Successfully saved. Do you want to <a href="#" @click.prevent="router.push(routes.main.uri)">edit this project</a>?
          </FormSuccess>
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
          <div class="mb-3 text-end">
            <LockableButton type="submit" class="btn btn-primary" :locked="isLocked">Save</LockableButton>
          </div>
        </div>
        <div class="col"></div>
      </fieldset>
    </form>
  </div>
</template>