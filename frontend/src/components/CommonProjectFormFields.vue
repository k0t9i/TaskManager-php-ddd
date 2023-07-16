<script setup>
import Datepicker from 'vue3-datepicker';

const props = defineProps({
  project: {
    required: true
  }
})
</script>

<template>
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" required="required" class="form-control" v-model="project.name">
  </div>
  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="10" v-model="project.description"></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">Finish Date</label>
    <Datepicker class="form-control" typeable required v-model="project.finishDate" />
  </div>
</template>