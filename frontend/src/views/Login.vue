<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="card w-96 bg-base-100 shadow-xl">
      <div class="card-body">
        <h2 class="card-title text-center">SkillsLink Login</h2>
        <div v-if="error" class="alert alert-error shadow-lg">
          <div>{{ error }}</div>
        </div>
        <form @submit.prevent="handleLogin">
          <div class="form-control">
            <label class="label">
              <span class="label-text">Username</span>
            </label>
            <input
              v-model="username"
              type="text"
              placeholder="Enter username"
              class="input input-bordered"
              required
            />
          </div>
          <div class="form-control mt-4">
            <label class="label">
              <span class="label-text">Password</span>
            </label>
            <input
              v-model="password"
              type="password"
              placeholder="Enter password"
              class="input input-bordered"
              required
            />
          </div>
          <div class="card-actions justify-center mt-6">
            <button 
              type="submit" 
              class="btn btn-primary"
              :class="{ 'loading': isLoading }"
              :disabled="isLoading"
            >
              <span v-if="!isLoading">Login</span>
              <span v-else>Logging in...</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import authService from '@/services/auth.js';

export default {
  name: 'Login',
  data() {
    return {
      username: '',
      password: '',
      error: '',
      isLoading: false
    };
  },
  async mounted() {
    // Check if user is already logged in
    const authCheck = await authService.checkAuth();
    if (authCheck.loggedIn) {
      this.$router.push('/dashboard');
    }
  },
  methods: {
    async handleLogin() {
      this.isLoading = true;
      this.error = '';
      
      try {
        const result = await authService.login({
          username: this.username,
          password: this.password
        });
        
        if (result.success) {
          this.$router.push('/dashboard');
        } else {
          this.error = result.error;
        }
      } catch (error) {
        this.error = 'An unexpected error occurred';
      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>