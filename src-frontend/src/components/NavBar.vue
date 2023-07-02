<script setup>
import {useAuthStore} from "../stores/auth";
import routes from "../router/routes";
import AsyncNavBarUserInfo from "./AsyncNavBarUserInfo.vue";

const authStore = useAuthStore();
</script>

<template>
  <nav class="navbar navbar-dark bg-dark px-4 navbar-expand-md">
    <div class="container">
      <RouterLink class="navbar-brand" :to="routes.main.uri">Task Manager</RouterLink>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navBar" aria-controls="navBar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navBar">
        <ul class="navbar-nav ms-auto mb-2 mb-md-0">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <Suspense>
                <AsyncNavBarUserInfo />
                <template #fallback>
                  <span>
                    <div class="spinner-grow spinner-grow-sm text-light mr1" role="status" /> loading...
                  </span>
                </template>
              </Suspense>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdown">
              <li><RouterLink class="dropdown-item" :to="routes.profile.uri">Profile</RouterLink></li>
              <li><hr class="dropdown-divider"></li>
              <li><a @click.prevent="authStore.logout()" href="#" class="dropdown-item">Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>