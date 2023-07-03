<script setup>
import AsyncProfileForm from "../components/AsyncProfileForm.vue";
</script>

<template>
  <div class="container-md">
    <h3 class="mt-4">Profile</h3>
    <Suspense>
      <AsyncProfileForm />
      <template #fallback>
        <span>
          <div class="spinner-border spinner-border-sm text-dark mx-1" role="status" />Loading...
        </span>
      </template>
    </Suspense>
  </div>
</template>

<style scoped>

</style>