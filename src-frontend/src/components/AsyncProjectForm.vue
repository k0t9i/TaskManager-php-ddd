<script setup>
import FormSuccess from "../components/FormSuccess.vue";
import LockableButton from "../components/LockableButton.vue";
import FormError from "../components/FormError.vue";
import {reactive, ref} from "vue";
import axiosInstance from "../helpers/axios";
import Datepicker from 'vue3-datepicker';
import router from "../router";
import ProjectStatus from "./ProjectStatus.vue";

const error = ref();
const projectId = ref();
const success = ref(false);
const project = reactive({
  name: '',
  description: '',
  finishDate: null,
  status: 1
});
const isLocked = ref(false);

const props = defineProps({
  id: {
    type: String,
    default: null
  }
});

if (props.id) {
  await axiosInstance.get('/projects/' + props.id).then((response) => {
    project.name = response.data.name;
    project.description = response.data.description;
    project.finishDate = new Date(response.data.finishDate);
    project.status = response.data.status;
    return response;
  });
}

function onSubmit() {
  error.value = '';
  projectId.value = null;
  success.value = false;
  isLocked.value = true;

  return axiosInstance
      .request({
        'url': '/projects/' + (props.id ? `${props.id}/` : ''),
        'method': props.id ? 'patch' : 'post',
        'data': project
      })
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
  success.value = true;

  if (!props.id) {
    project.name = '';
    project.description = '';
    project.finishDate = null;
    project.status = 1;
  }

  return response;
}
</script>

<template>
  <form @submit.prevent="onSubmit">
    <fieldset class="row mt-4" :disabled="isLocked || project.status === 0">
      <div class="col"></div>
      <div class="col-md-6">
        <FormError :error="error" />
        <FormSuccess v-if="success">
          Successfully saved.
          <span v-if="projectId">
            Do you want to <RouterLink :to="{name: 'edit_project', params: { id: projectId }}">edit this project</RouterLink>?
          </span>
        </FormSuccess>
        <div class="mb-3" v-if="props.id">
          <label class="form-label">Status</label>
          <div class="h5">
            <ProjectStatus :status="project.status" />
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
        <div class="mb-3 text-end">
          <LockableButton type="submit" class="btn btn-primary" :locked="isLocked">Save</LockableButton>
        </div>
      </div>
      <div class="col"></div>
    </fieldset>
  </form>
</template>