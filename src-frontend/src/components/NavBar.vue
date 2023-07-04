<script setup>
import {useAuthStore} from "../stores/auth";
import AsyncNavBarUserInfo from "./AsyncNavBarUserInfo.vue";

const authStore = useAuthStore();
</script>

<template>
  <nav class="navbar navbar-dark bg-dark px-4 navbar-expand-md">
    <div class="container">
      <RouterLink class="navbar-brand" :to="{name: 'main'}">Task Manager</RouterLink>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navBar" aria-controls="navBar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navBar">
        <ul class="navbar-nav mr-auto mb-2 mb-md-0">
          <li class="nav-item dropdown">
            <RouterLink :to="{name: 'create_project'}" class="nav-link">Create project</RouterLink>
          </li>
        </ul>
        <ul class="navbar-nav ms-auto mb-2 mb-md-0">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <Suspense>
                <AsyncNavBarUserInfo />
                <template #fallback>
                  <span>
                    <div class="spinner-border spinner-border-sm text-light mx-1" role="status" />Loading...
                  </span>
                </template>
              </Suspense>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdown">
              <li><RouterLink class="dropdown-item" :to="{name: 'profile'}">Profile</RouterLink></li>
              <li><hr class="dropdown-divider"></li>
              <li><a @click.prevent="authStore.logout()" href="#" class="dropdown-item">Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>