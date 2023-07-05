<script setup>
defineProps({
  status: Number
});
</script>

<template>
  <span class="badge" :class="{'bg-success': status === 1, 'bg-danger': status === 0}">
    <span v-if="status === 1">Active</span><span v-else>Closed</span>
  </span>
</template>