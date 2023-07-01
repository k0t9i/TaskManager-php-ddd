<script setup>
import {ref} from "vue";
import routes from '../router/routes';
import router from '../router';
import { useAuthStore } from '../stores/auth';

const error = ref();
const user = ref({
  email: '',
  password: ''
})

function onSubmit() {
  error.value = '';

  const authStore = useAuthStore();

  return authStore
      .login(user.value.email, user.value.password)
      .catch(e => {
        error.value = e.response.data.message;
        console.log(error);
      });
}
</script>

<template>
  <div class="vh-100 d-flex align-items-center justify-content-center">
    <div class="container">
      <form @submit.prevent="onSubmit" class="row">
        <div class="col"></div>
        <div class="col-lg-4">
          <div v-if="error" class="alert alert-danger mb-3" role="alert">
            {{ error }}
          </div>
          <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" class="form-control" v-model="user.email">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control"  v-model="user.password">
          </div>
          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary">Sign In</button>
          </div>
          <div class="text-center mb-3">
            <p><a @click.prevent="router.push(routes.register.uri)" href="#">Register</a></p>
          </div>
        </div>
        <div class="col"></div>
      </form>
    </div>
  </div>
</template>

<style scoped>

</style>