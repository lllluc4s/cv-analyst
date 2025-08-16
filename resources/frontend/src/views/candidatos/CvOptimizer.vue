<template>
  <div class="cv-optimizer-page">
    <CvOptimizer />
  </div>
</template>

<script setup lang="ts">
import CvOptimizer from '@/components/CvOptimizer.vue'
</script>

<style scoped>
.cv-optimizer-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px 0;
}
</style>
