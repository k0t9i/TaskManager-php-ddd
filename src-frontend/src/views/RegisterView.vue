<script setup>
import routes from '../router/routes';
import router from "../router";
import {reactive, ref} from "vue";
import ajaxWrapper from "../helpers/ajaxWrapper";
import FormError from "../components/FormError.vue";
import FormSuccess from "../components/FormSuccess.vue";

const error = ref('');
const registeredEmail = ref('');
const user = reactive({
  email: '',
  firstname: '',
  lastname: '',
  password: '',
  repeatPassword: ''
});

function onSubmit() {
  error.value = '';
  registeredEmail.value = '';

  return ajaxWrapper
      .post(`${import.meta.env.VITE_API_URL}/security/register/`, {
        email: user.email,
        firstname: user.firstname,
        lastname: user.lastname,
        password: user.password,
        repeatPassword: user.repeatPassword
      })
      .then(onSuccess)
      .catch((e) => {
        error.value = e.response.data.message;
      });
}

function onSuccess(response) {
  registeredEmail.value = user.email;
  user.email = '';
  user.firstname = '';
  user.lastname = '';
  user.password = '';
  user.repeatPassword = '';

  return response;
}
</script>

<template>
  <div class="vh-100 d-flex align-items-center justify-content-center">
    <div class="container">
      <form class="row" @submit.prevent="onSubmit">
        <div class="col"></div>
        <div class="col-md-4">
          <FormError :error="error" />
          <FormSuccess v-if="registeredEmail">
            User "{{ registeredEmail }}" registered successfully. You can now <a @click.prevent="router.push(routes.login.uri)" href="#">sign in</a>.
          </FormSuccess>
          <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" name="email" required="required" class="form-control" v-model="user.email">
          </div>
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
            <input type="password" name="password" required="required" class="form-control" v-model="user.password">
          </div>
          <div class="mb-3">
            <label class="form-label">Repeat password</label>
            <input type="password" name="repeatPassword" required="required" class="form-control" v-model="user.repeatPassword">
          </div>
          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary">Sign Out</button>
          </div>
          <div class="text-center mb-3">
            <p><a @click.prevent="router.push(routes.login.uri)" href="#">Sign In</a></p>
          </div>
        </div>
        <div class="col"></div>
      </form>
    </div>
  </div>
</template>

<style scoped>

</style>