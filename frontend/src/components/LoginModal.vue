<template>
  <div class="modal" :class="{ 'modal-open': isOpen }" role="dialog">
    <div class="modal-box glass max-w-md">
      <h3 class="font-bold text-2xl text-center mb-4">SkillsLink Login</h3>
      <div v-if="error" class="alert alert-error shadow-lg mb-4">
        <span>{{ error }}</span>
      </div>
      <form @submit.prevent="handleLogin">
        <div class="form-control">
          <label class="label">
            <span class="label-text">Email or Student ID</span>
          </label>
          <input
            v-model="login"
            type="text"
            placeholder="Enter email or student ID"
            class="input input-bordered glass"
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
            class="input input-bordered glass"
            required
          />
        </div>
        <div class="card-actions justify-center mt-6">
          <button 
            type="submit" 
            class="btn btn-primary glass"
            :class="{ 'loading': isLoading }"
            :disabled="isLoading"
          >
            <span v-if="!isLoading">Login</span>
            <span v-else>Logging in...</span>
          </button>
        </div>
      </form>
      <div class="text-center mt-4">
        <button
          class="btn btn-ghost text-sm"
          @click="switchToRegister"
        >
          New alumni? Register here
        </button>
      </div>
      <div class="modal-action">
        <button class="btn btn-sm btn-circle absolute right-2 top-2" @click="closeModal">✕</button>
      </div>
    </div>
    <div class="modal-backdrop" @click="closeModal"></div>
  </div>
</template>

<script>
import authService from '@/services/auth.js';

export default {
  name: 'LoginModal',
  props: {
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'switch-to-register', 'login-success'],
  data() {
    return {
      login: '', // Can be email or student ID
      password: '',
      error: '',
      isLoading: false
    };
  },
  watch: {
    isOpen(newVal) {
      if (newVal) {
        this.resetForm();
      }
    }
  },
  methods: {
    async handleLogin() {
      this.isLoading = true;
      this.error = '';
      
      try {
        const result = await authService.login({
          login: this.login,
          password: this.password
        });
        
        if (result.success) {
          this.$emit('login-success');
          this.closeModal();
        } else {
          this.error = result.error;
        }
      } catch (error) {
        this.error = 'An unexpected error occurred';
      } finally {
        this.isLoading = false;
      }
    },
    
    closeModal() {
      this.$emit('close');
    },
    
    switchToRegister() {
      this.$emit('switch-to-register');
    },
    
    resetForm() {
      this.login = '';
      this.password = '';
      this.error = '';
      this.isLoading = false;
    }
  }
};
</script>

<style scoped>
.glass {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}
</style>
