<script setup>
import routes from '../router/routes';
import router from "../router";
</script>

<template>
  <div class="vh-100 d-flex align-items-center justify-content-center">
    <div class="container">
      <div class="row">
        <div class="col"></div>
        <div class="col-lg-4">
          <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">First name</label>
            <input type="text" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Last name</label>
            <input type="text" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Repeat password</label>
            <input type="password" class="form-control">
          </div>
          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary">Sign In</button>
          </div>
          <div class="text-center mb-3">
            <p><a @click.prevent="router.push(routes.login.uri)" href="#">Login</a></p>
          </div>
        </div>
        <div class="col"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>