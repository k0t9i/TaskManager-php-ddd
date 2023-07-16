<script setup>
defineProps({
  error: {
    type: String,
    required: true,
    default: ''
  }
})
</script>

<template>
  <div v-if="error" class="alert alert-danger mb-3" role="alert">
    {{ error }}
  </div>
</template>