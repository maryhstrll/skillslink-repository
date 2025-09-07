<template>
  <div class="modal" :class="{ 'modal-open': isOpen }" role="dialog">
    <div class="modal-box glass max-w-50%">
      <h3 class="text1 font-bold text-2xl text-center mb-4">
        Alumni Registration
      </h3>
      <div v-if="error" class="alert alert-error shadow-lg mb-4">
        <span>{{ error }}</span>
      </div>
      <div v-if="success" class="alert alert-info shadow-lg mb-4">
        <div>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            class="stroke-current flex-shrink-0 w-6 h-6"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            ></path>
          </svg>
          <span>{{ successMessage }}</span>
        </div>
      </div>
      <form @submit.prevent="handleRegister" v-if="!success">
        <div class="grid grid-cols-3 gap-4">
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
              <span class="label-text">Middle Name</span>
            </label>
            <input
              v-model="form.middleName"
              type="text"
              placeholder="Enter middle name (optional)"
              class="input input-bordered glass"
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
        <!-- Birthdate -->
        <div class="form-control mt-4">
          <label class="label">
            <span class="label-text">Birthdate</span>
          </label>
          <input
            v-model="form.birthdate"
            type="date"
            class="input input-bordered glass"
            required
          />
        </div>
        <!-- Sex -->
        <div class="form-control mt-4 text-[#0b1524]">
          <label class="label">
            <span class="label-text">Gender</span>
          </label>
          <select v-model="form.gender" class="select select-bordered glass" required>
            <option value="" disabled>Select gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>
        <!-- Program Selection -->
        <div class="form-control mt-4 text-[#0b1524]">
          <label class="label">
            <span class="label-text flex items-center gap-2">Program
              <span v-if="isProgramsLoading" class="loading loading-spinner loading-xs" />
            </span>
          </label>
          <select
            v-model="form.programId"
            class="select select-bordered glass"
            :disabled="isProgramsLoading"
            required
          >
            <option value="" disabled>Select your program</option>
            <option v-for="program in programs" :key="program.id || program.program_id" :value="program.id || program.program_id">
              {{ program.name || program.program_name }}
            </option>
          </select>
          <div v-if="programsError" class="text-error text-sm mt-1 flex flex-col gap-1">
            <span>{{ programsError }}</span>
            <button type="button" class="link link-primary text-xs self-start" @click="fetchPrograms">Retry</button>
          </div>
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
              class="input input-bordered glass w-full pr-12"
              required
            />
            <button
              type="button"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
              @click="togglePasswordVisibility"
            >
              <IconEye
                v-if="!showPassword"
                class="w-5 h-5 text-gray-400 hover:text-gray-600 transition-colors duration-200"
              />
              <IconEyeOff
                v-else
                class="w-5 h-5 text-gray-400 hover:text-gray-600 transition-colors duration-200"
              />
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
              class="input input-bordered glass w-full pr-12"
              required
            />
            <button
              type="button"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
              @click="toggleConfirmPasswordVisibility"
            >
              <IconEye
                v-if="!showConfirmPassword"
                class="w-5 h-5 text-gray-400 hover:text-gray-600 transition-colors duration-200"
              />
              <IconEyeOff
                v-else
                class="w-5 h-5 text-gray-400 hover:text-gray-600 transition-colors duration-200"
              />
            </button>
          </div>
          <div
            v-if="
              form.confirmPassword && form.password !== form.confirmPassword
            "
            class="text-error text-sm mt-1"
          >
            Passwords do not match
          </div>
        </div>
        <div class="card-actions justify-center mt-6">
          <button
            type="submit"
            class="btn btn-primary glass"
            :class="{ loading: isLoading }"
            :disabled="isLoading"
          >
            <span v-if="!isLoading">Register</span>
            <span v-else>Registering...</span>
          </button>
        </div>
      </form>
      <div class="text-center mt-4" v-if="!success">
        <button class="btn btn-ghost text-sm" @click="switchToLogin">
          Already have an account? Login
        </button>
      </div>
      <div class="text-center mt-4" v-if="success">
        <button class="btn btn-primary glass" @click="switchToLogin">
          Go to Login
        </button>
      </div>
      <div class="modal-action">
        <button
          class="btn btn-sm btn-circle absolute right-2 top-2"
          @click="closeModal"
        >
          ✕
        </button>
      </div>
    </div>
    <div class="modal-backdrop" @click="closeModal"></div>
  </div>
</template>

<script>
import authService from "@/services/auth.js";
import programsService from "@/services/programs.js";
import { Eye as IconEye, EyeOff as IconEyeOff } from 'lucide-vue-next';

export default {
  name: "RegisterModal",
  components: {
    IconEye,
    IconEyeOff
  },
  props: {
    isOpen: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["close", "switch-to-login", "register-success"],
  data() {
    return {
      form: {
        firstName: "",
        middleName: "",
        lastName: "",
        studentId: "",
        birthdate: "",
        gender: "",
        password: "",
        confirmPassword: "",
        email: "",
        programId: "",
        role: "alumni", // Default role for alumni registration
      },
      error: "",
      success: false,
      successMessage: "",
      isLoading: false,
      showPassword: false,
      showConfirmPassword: false,
      programs: [],
      isProgramsLoading: false,
      programsError: "",
    };
  },
  watch: {
    isOpen(newVal) {
      if (newVal) {
        this.resetForm();
        this.fetchPrograms();
      }
    },
  },
  methods: {
    async handleRegister() {
      this.isLoading = true;
      this.error = "";
      this.success = false;

      // Validate password confirmation
      if (this.form.password !== this.form.confirmPassword) {
        this.error = "Passwords do not match";
        this.isLoading = false;
        return;
      }

      try {
        const result = await authService.register({
          firstName: this.form.firstName,
          middleName: this.form.middleName,
          lastName: this.form.lastName,
          studentId: this.form.studentId,
          birthdate: this.form.birthdate,
          gender: this.form.gender,
          password: this.form.password,
          email: this.form.email,
          programId: this.form.programId,
          role: this.form.role,
        });

        if (result.success) {
          this.success = true;
          this.successMessage =
            result.message ||
            "Registration successful! Your account is pending approval.";
          this.$emit("register-success");
        } else {
          this.error = result.error;
        }
      } catch (error) {
        this.error = "An unexpected error occurred";
      } finally {
        this.isLoading = false;
      }
    },

    closeModal() {
      this.$emit("close");
    },

    switchToLogin() {
      this.$emit("switch-to-login");
    },

    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },

    toggleConfirmPasswordVisibility() {
      this.showConfirmPassword = !this.showConfirmPassword;
    },

    resetForm() {
      this.form = {
        firstName: "",
        middleName: "",
        lastName: "",
        studentId: "",
        birthdate: "",
        gender: "",
        password: "",
        confirmPassword: "",
        email: "",
        programId: "",
        role: "alumni", // Default role for alumni
      };
      this.error = "";
      this.success = false;
      this.successMessage = "";
      this.isLoading = false;
      this.showPassword = false;
      this.showConfirmPassword = false;
      this.programsError = "";
    },

    async fetchPrograms() {
      this.isProgramsLoading = true;
      this.programsError = "";
      try {
  this.programs = await programsService.list();
      } catch (e) {
        console.error("Failed to fetch programs", e);
        this.programsError = "Unable to load programs";
      } finally {
        this.isProgramsLoading = false;
      }
    },
  },
};
</script>

<style scoped>
.glass {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.modal-box {
  position: relative;
  z-index: 20; /* above backdrop */
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  z-index: 10;
}

select.select {
  pointer-events: auto;
}

.relative {
  position: relative;
}
.relative button {
  z-index: 10;
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

.text-gray-400,
.label-text,
.text1 {
  color: var(--color-text-primary);
}

.input {
  color: var(--color-text-primary);
  margin-top: 5px;
}
.cursor-pointer {
  cursor: pointer;
}

.btn-primary:hover {
  background: var(--color-primary);
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
