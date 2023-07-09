<script setup>
import {ref} from "vue";

const props = defineProps({
  value: {
    type: Date,
    required: true
  },
  withTime: {
    type: Boolean,
    default: false
  }
});

const options = props.withTime ? {dateStyle: 'long', timeStyle: 'long'} : {dateStyle: 'long'};
const stringValue = new Intl.DateTimeFormat('default', options).format(props.value);
</script>

<template>
  {{ stringValue }}
</template>