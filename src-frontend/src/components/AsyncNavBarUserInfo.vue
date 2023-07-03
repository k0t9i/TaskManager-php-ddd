<script setup>
import {ref} from "vue";
import axiosInstance from "../helpers/axios";

const email = ref();
await axiosInstance.get('/users/').then((response) => {
  email.value = response.data.email;
  return response;
});
</script>

<template>
  {{ email }}
</template>