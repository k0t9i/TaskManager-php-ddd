<script setup>
import FormSuccess from "./FormSuccess.vue";
import FormError from "./FormError.vue";
import {ref, toRef} from "vue";
import LockableButton from "./LockableButton.vue";
import {useUserStore} from "../stores/user";

const success = ref(false);
const userStore = useUserStore();
await userStore.load();
const user = userStore.user;

function onSubmit() {
  success.value = false;

  return userStore.save()
      .then(onSuccess);
}

function onSuccess(response) {
  if (!userStore.error) {
    success.value = true;
    user.password = '';
    user.repeatPassword = '';
  }

  return response;
}
</script>

<template>
  <form @submit.prevent="onSubmit">
    <fieldset class="row mt-4" :disabled="userStore.locked">
      <div class="col"></div>
      <div class="col-md-9">
        <FormError :error="userStore.error" />
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
          <LockableButton type="submit" class="btn btn-primary" :locked="userStore.locked">Save</LockableButton>
        </div>
      </div>
    </fieldset>
  </form>
</template>