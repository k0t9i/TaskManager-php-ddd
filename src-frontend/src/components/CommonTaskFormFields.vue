<script setup>
import Datepicker from 'vue3-datepicker';

const props = defineProps({
  task: {
    required: true
  },
  project: {
    required: true
  }
})
</script>

<template>
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" required="required" class="form-control" v-model="task.name">
  </div>
  <div class="mb-3">
    <label class="form-label">Brief info</label>
    <textarea name="brief" class="form-control" rows="5" v-model="task.brief"></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="10" v-model="task.description"></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">Start Date</label>
    <Datepicker class="form-control" typeable required v-model="task.startDate" :upper-limit="task.finishDate && task.finishDate < project.finishDate ? task.finishDate : project.finishDate"  />
  </div>
  <div class="mb-3">
    <label class="form-label">Finish Date</label>
    <Datepicker class="form-control" typeable required v-model="task.finishDate" :upper-limit="project.finishDate" />
  </div>
</template>