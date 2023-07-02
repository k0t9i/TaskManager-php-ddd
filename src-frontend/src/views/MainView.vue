<script setup>
import {useAuthStore} from "../stores/auth";
const authStore = useAuthStore();
</script>

<template>
<a @click.prevent="authStore.logout()" href="#">Logout</a>
</template>

<style scoped>

</style>