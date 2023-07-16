<script setup>
import {useUserProjectsStore} from "../stores/userProjects";

const userProjectsStore = useUserProjectsStore();
await userProjectsStore.load();
</script>

<template>
  ({{ userProjectsStore.countAll }})
</template>