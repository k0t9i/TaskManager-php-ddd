<script setup>
import FormSuccess from "./FormSuccess.vue";
import FormError from "./FormError.vue";
import {reactive, ref} from "vue";
import LockableButton from "./LockableButton.vue";
import axiosInstance from "../helpers/axios";

const error = ref();
const success = ref(false);
const user = reactive({
  firstname: '',
  lastname: '',
  password: '',
  repeatPassword: ''
});
const isLocked = ref(false);

function onSubmit() {
  error.value = '';
  success.value = false;
  isLocked.value = true;

  return axiosInstance
      .patch('/users/', {
        firstname: user.firstname,
        lastname: user.lastname,
        password: user.password,
        repeatPassword: user.repeatPassword
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
  success.value = true;
  user.password = '';
  user.repeatPassword = '';

  return response;
}

await axiosInstance.get('/users/').then((response) => {
  user.firstname = response.data.firstname;
  user.lastname = response.data.lastname;
  return response;
});
</script>

<template>
  <form @submit.prevent="onSubmit">
    <fieldset class="row mt-4" :disabled="isLocked">
      <div class="col"></div>
      <div class="col-md-6">
        <FormError :error="error" />
        <FormSuccess v-if="success">
          Successfully saved.
        </FormSuccess>
        <div class="mb-3">
          <label class="form-label">First name</label>
          <input type="text" name="firstname" required="required" class="form-control" v-model="user.firstname">
        </div>
        <div class="mb-3">
          <label class="form-label">Last name</label>
          <input type="text" name="lastname" required="required" class="form-control" v-model="user.lastname">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" v-model="user.password">
        </div>
        <div class="mb-3">
          <label class="form-label">Repeat password</label>
          <input type="password" name="repeatPassword" class="form-control" v-model="user.repeatPassword">
        </div>
        <div class="mb-3 text-end">
          <LockableButton type="submit" class="btn btn-primary" :locked="isLocked">Save</LockableButton>
        </div>
      </div>
      <div class="col"></div>
    </fieldset>
  </form>
</template>