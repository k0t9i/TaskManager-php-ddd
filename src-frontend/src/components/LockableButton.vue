<script setup>
defineProps({
  locked: {
    type: Boolean,
    default: false
  }
})
</script>

<template>
  <button v-bind="$attrs" :disabled="locked">
    <slot v-if="locked" name="lockedContent">
      <span><span class="spinner-border spinner-border-sm mx-1" role="status"></span>Loading...</span>
    </slot>
    <slot v-else></slot>
  </button>
</template>