<template>
  <div class="modal" :class="{ 'modal-open': isOpen }" role="dialog">
    <div class="modal-box glass max-w-md">
      <h3 class="text1 font-bold text-2xl text-center mb-4">SkillsLink Login</h3>
      <!-- Error messages now handled by MessageContainer -->
      <form @submit.prevent="handleLogin">
        <div class="form-control">
          <label class="label">
            <span class="label-text">Email or ID</span>
          </label>
          <input
            v-model="login"
            type="text"
            placeholder="Enter email or ID"
            class="input input-bordered glass w-full"
            required
          />
        </div>
        <div class="form-control mt-4">
          <label class="label">
            <span class="label-text">Password</span>
          </label>
          <div class="relative">
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Enter password"
              class="input input-bordered glass w-full pr-12"
              required
            />
            <button
              type="button"
              class="eye absolute inset-y-0 right-0 pr-3 flex items-center justify-center"
              @click="togglePasswordVisibility"
            >
              <IconEye 
                v-if="!showPassword"
                class="w-5 h-5 text-navy hover:text-gray-600 transition-colors duration-200"
              />
              <IconEyeOff 
                v-else
                class="w-5 h-5 text-navy hover:text-gray-600 transition-colors duration-200"
              />
            </button>
          </div>
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
      <div class="text-center mt-6">
        <button
          class="btn btn-ghost text-sm"
          @click="switchToRegister"
        >
          Register here
        </button>
      </div>
      <div class="modal-action ">
        <button class="btn btn-sm btn-circle absolute right-2 top-2" @click="closeModal">✕</button>
      </div>
    </div>
    <div class="modal-backdrop" @click="closeModal"></div>
  </div>
</template>

<script>
import authService from '@/services/auth.js';
import { messageService } from '@/services/messageService.js';

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
      showPassword: false,
      isLoading: false
      // Removed error - now using messageService
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
      
      // Basic client-side validation
      if (!this.login.trim()) {
        messageService.toast('⚠️ Please enter your email or ID', 'warning');
        this.isLoading = false;
        return;
      }
      
      if (!this.password.trim()) {
        messageService.toast('⚠️ Please enter your password', 'warning');
        this.isLoading = false;
        return;
      }
      
      try {
        const result = await authService.login({
          login: this.login,
          password: this.password
        });
        
        if (result.success) {
          // Clear any previous error messages
          messageService.clear();
          messageService.toast('Login successful! Welcome back.', 'success');
          this.$emit('login-success');
          this.closeModal();
        } else {
          // Show warning message for invalid credentials
          const errorMessage = result.error || 'Login failed';
          console.log('Login error message:', errorMessage); // Debug log
          if (errorMessage.toLowerCase().includes('invalid credentials') || 
              errorMessage.toLowerCase().includes('password')) {
            messageService.toast('⚠️ Invalid email/ID or password. Please check your credentials.', 'warning');
          } else if (errorMessage.toLowerCase().includes('pending')) {
            console.log('Showing pending approval message'); // Debug log
            messageService.alert(errorMessage, 'warning', 'Account Pending');
          } else if (errorMessage.toLowerCase().includes('rejected')) {
            messageService.alert(errorMessage, 'error', 'Account Rejected');
          } else {
            messageService.alert(errorMessage, 'error', 'Login Error');
          }
        }
      } catch (error) {
        console.error('Login error:', error);
        messageService.alert('An unexpected error occurred. Please try again.', 'error', 'Login Error');
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
    
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },
    
    resetForm() {
      this.login = '';
      this.password = '';
      this.showPassword = false;
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
.label, .text1  {
 color: var(--color-navy);
}
input.input {
  color: var(--color-surface-alt);
  margin-top: 5px;
}
/* to ensure that the eye icon is properly positioned and clickable */
.relative input {
  padding-right: 3rem;
}

.relative button {
  z-index: 10;
  color: var(--color-navy);
}

.relative button:focus {
  outline: none;
}

.relative button i {
  cursor: pointer;
  user-select: none;
}

</style>
