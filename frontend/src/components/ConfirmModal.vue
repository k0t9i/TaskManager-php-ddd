<script setup>
import confirmModal from "./confirmModal";
import {onMounted} from "vue";

const config = confirmModal.getConfig();

onMounted (() => {
  confirmModal.init();
});
</script>

<template>
  <div class="modal fade" tabindex="-1" id="confirmModal" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ config.title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>{{ config.body }}</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" @click.prevent="confirmModal.confirm">OK</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>