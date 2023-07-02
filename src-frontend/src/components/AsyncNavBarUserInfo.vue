<script setup>
import ajaxWrapper from "../helpers/ajaxWrapper";
import {ref} from "vue";

const email = ref();
await ajaxWrapper.get(`${import.meta.env.VITE_API_URL}/users/`).then((response) => {
  email.value = response.data.email;
  return response;
});
</script>

<template>
  {{ email }}
</template>