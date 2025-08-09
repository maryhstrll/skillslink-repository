<script setup>
import MessageContainer from './components/MessageContainer.vue';

// Remove the direct Dashboard import - let the router handle it
</script>

<template>
  <div data-theme="skillslink" class="min-h-screen bg-base-100">
    <router-view />
    <MessageContainer></MessageContainer>
  </div>
</template>

<style scoped></style>
