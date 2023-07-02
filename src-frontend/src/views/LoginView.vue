<script setup>
import {reactive, ref} from "vue";
import routes from '../router/routes';
import router from '../router';
import { useAuthStore } from '../stores/auth';
import FormError from "../components/FormError.vue";

const error = ref('');
const user = reactive({
  email: '',
  password: ''
})

function onSubmit() {
  error.value = '';

  const authStore = useAuthStore();

  return authStore
      .login(user.email, user.password)
      .catch((e) => {
        error.value = e.response.data.message;
      });
}
</script>

<template>
  <div class="vh-100 d-flex align-items-center justify-content-center">
    <div class="container">
      <form @submit.prevent="onSubmit" class="row">
        <div class="col"></div>
        <div class="col-lg-4">
          <FormError :error="error" />
          <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" name="email" required="required" class="form-control" v-model="user.email">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" required="required" class="form-control"  v-model="user.password">
          </div>
          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary">Sign In</button>
          </div>
          <div class="text-center mb-3">
            <p><a @click.prevent="router.push(routes.register.uri)" href="#">Sign Out</a></p>
          </div>
        </div>
        <div class="col"></div>
      </form>
    </div>
  </div>
</template>

<style scoped>

</style>