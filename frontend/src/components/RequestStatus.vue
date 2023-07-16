<script setup>
import {ref} from "vue";

const props = defineProps({
  status: Number
});
</script>

<template>
  <span class="badge" :class="{'bg-primary': status === 0, 'bg-success': status === 1, 'bg-danger': status === 2}">
    <span v-if="status === 0">Pending</span>
    <span v-else-if="status === 1">Confirmed</span>
    <span v-else-if="status === 2">Rejected</span>
  </span>
</template>