<template>
  <div class="modal" :class="{ 'modal-open': isOpen }" role="dialog">
    <div class="modal-box glass max-w-md">
      <h3 class="font-bold text-2xl text-center mb-4">Alumni Registration</h3>
      <div v-if="error" class="alert alert-error shadow-lg mb-4">
        <span>{{ error }}</span>
      </div>
      <div v-if="success" class="alert alert-info shadow-lg mb-4">
        <div>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current flex-shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <span>{{ successMessage }}</span>
        </div>
      </div>
      <form @submit.prevent="handleRegister" v-if="!success">
        <div class="grid grid-cols-2 gap-4">
          <div class="form-control">
            <label class="label">
              <span class="label-text">First Name</span>
            </label>
            <input
              v-model="form.firstName"
              type="text"
              placeholder="Enter first name"
              class="input input-bordered glass"
              required
            />
          </div>
          <div class="form-control">
            <label class="label">
              <span class="label-text">Last Name</span>
            </label>
            <input
              v-model="form.lastName"
              type="text"
              placeholder="Enter last name"
              class="input input-bordered glass"
              required
            />
          </div>
        </div>
        <div class="form-control mt-4">
          <label class="label">
            <span class="label-text">Student ID</span>
          </label>
          <input
            v-model="form.studentId"
            type="text"
            placeholder="Enter your student ID"
            class="input input-bordered glass"
            required
          />
        </div>
        <div class="form-control mt-4">
          <label class="label">
            <span class="label-text">Email</span>
          </label>
          <input
            v-model="form.email"
            type="email"
            placeholder="Enter email"
            class="input input-bordered glass"
            required
          />
        </div>
        <div class="form-control mt-4">
          <label class="label">
            <span class="label-text">Password</span>
          </label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Enter password"
              class="input input-bordered glass pr-12"
              required
            />
            <button
              type="button"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
              @click="togglePasswordVisibility"
            >
              <svg
                class="h-5 w-5 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  v-if="!showPassword"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
                <path
                  v-if="!showPassword"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
                <path
                  v-if="showPassword"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"
                />
              </svg>
            </button>
          </div>
        </div>
        <div class="form-control mt-4">
          <label class="label">
            <span class="label-text">Confirm Password</span>
          </label>
          <div class="relative">
            <input
              v-model="form.confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'"
              placeholder="Confirm your password"
              class="input input-bordered glass pr-12"
              required
            />
            <button
              type="button"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
              @click="toggleConfirmPasswordVisibility"
            >
              <svg
                class="h-5 w-5 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  v-if="!showConfirmPassword"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
                <path
                  v-if="!showConfirmPassword"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
                <path
                  v-if="showConfirmPassword"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"
                />
              </svg>
            </button>
          </div>
          <div v-if="form.confirmPassword && form.password !== form.confirmPassword" class="text-error text-sm mt-1">
            Passwords do not match
          </div>
        </div>
        <div class="card-actions justify-center mt-6">
          <button
            type="submit"
            class="btn btn-primary glass"
            :class="{ 'loading': isLoading }"
            :disabled="isLoading"
          >
            <span v-if="!isLoading">Register</span>
            <span v-else>Registering...</span>
          </button>
        </div>
      </form>
      <div class="text-center mt-4" v-if="!success">
        <button
          class="btn btn-ghost text-sm"
          @click="switchToLogin"
        >
          Already have an account? Login
        </button>
      </div>
      <div class="text-center mt-4" v-if="success">
        <button
          class="btn btn-primary glass"
          @click="switchToLogin"
        >
          Go to Login
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
  name: 'RegisterModal',
  props: {
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'switch-to-login', 'register-success'],
  data() {
    return {
      form: {
        firstName: '',
        lastName: '',
        studentId: '',
        password: '',
        confirmPassword: '',
        email: '',
        role: 'alumni' // Default role for alumni registration
      },
      error: '',
      success: false,
      successMessage: '',
      isLoading: false,
      showPassword: false,
      showConfirmPassword: false
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
    async handleRegister() {
      this.isLoading = true;
      this.error = '';
      this.success = false;

      // Validate password confirmation
      if (this.form.password !== this.form.confirmPassword) {
        this.error = 'Passwords do not match';
        this.isLoading = false;
        return;
      }

      try {
        const result = await authService.register({
          firstName: this.form.firstName,
          lastName: this.form.lastName,
          studentId: this.form.studentId,
          password: this.form.password,
          email: this.form.email,
          role: this.form.role
        });

        if (result.success) {
          this.success = true;
          this.successMessage = result.message || 'Registration successful! Your account is pending approval.';
          this.$emit('register-success');
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
    
    switchToLogin() {
      this.$emit('switch-to-login');
    },
    
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },
    
    toggleConfirmPasswordVisibility() {
      this.showConfirmPassword = !this.showConfirmPassword;
    },
    
    resetForm() {
      this.form = {
        firstName: '',
        lastName: '',
        studentId: '',
        password: '',
        confirmPassword: '',
        email: '',
        role: 'alumni' // Default role for alumni
      };
      this.error = '';
      this.success = false;
      this.successMessage = '';
      this.isLoading = false;
      this.showPassword = false;
      this.showConfirmPassword = false;
    }
  }
};
</script>

<style scoped>
.glass {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.relative {
  position: relative;
}

.absolute {
  position: absolute;
}

.inset-y-0 {
  top: 0;
  bottom: 0;
}

.right-0 {
  right: 0;
}

.pr-3 {
  padding-right: 0.75rem;
}

.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

.pr-12 {
  padding-right: 3rem;
}

.h-5 {
  height: 1.25rem;
}

.w-5 {
  width: 1.25rem;
}

.text-gray-400 {
  color: #9ca3af;
}

.cursor-pointer {
  cursor: pointer;
}

button[type="button"] {
  background: none;
  border: none;
  cursor: pointer;
}

button[type="button"]:hover svg {
  color: #6b7280;
}
</style>
