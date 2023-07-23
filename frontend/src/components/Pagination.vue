<script setup>
import {useRoute} from "vue-router";

const props = defineProps({
  metadata: {
    type: Object,
    required: true
  },
  locked: {
    type: Boolean,
    default: false
  }
});

const route = useRoute();
</script>

<template>
  <nav v-if="metadata.total > 1">
    <ul class="pagination justify-content-end">
      <li class="page-item" :class="{disabled: metadata.previous === null || props.locked}">
        <RouterLink active-class="" class="page-link" :to="{name: route.name, params: route.params, query: {page: metadata.previous}}">&laquo;</RouterLink>
      </li>
      <li class="page-item" :class="{active: pageNum === metadata.current, disabled: props.locked}" v-for="pageNum in metadata.total">
        <span class="page-link" v-if="pageNum === metadata.current">{{ pageNum }}</span>
        <RouterLink v-else active-class="" class="page-link" :to="{name: route.name, params: route.params, query: {page: pageNum}}">{{ pageNum }}</RouterLink>
      </li>
      <li class="page-item" :class="{disabled: metadata.next === null || props.locked}">
        <RouterLink active-class="" class="page-link" :to="{name: route.name, params: route.params, query: {page: metadata.next}}">&raquo;</RouterLink>
      </li>
    </ul>
  </nav>
</template>