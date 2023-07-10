<script setup>
import {useUserStore} from "../stores/user";
import {toRef} from "vue";

const userStore = useUserStore();
await userStore.load();
const user = userStore.user;
</script>

<template>
  {{ user.firstname }} {{ user.lastname }} ({{ user.email }})
</template>