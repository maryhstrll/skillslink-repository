<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold">SkillsLink Home</h1>
    <p>{{ message }}</p>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return { message: 'Loading...' };
  },
  mounted() {
    axios.get('/api/test')
      .then(response => {
        this.message = response.data.message;
      })
      .catch(error => {
        this.message = 'Error: ' + error.message;
      });
  },
};
</script>