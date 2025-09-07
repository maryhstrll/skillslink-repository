<template>
  <div
    class="min-h-screen bg-cover bg-center bg-no-repeat flex flex-col"
    :style="{ backgroundImage: `url(${backgroundImage})` }"
  >
    <!-- Navbar -->
    <div class="navbar bg-[#1E549F]">
      <div class="flex-1 sticky top-0 z-50">
        <a class="btn btn-ghost text-2xl font-bold text-white hover:bg-[#081F37]">SkillsLink</a>
      </div>
    </div>

    <!-- Hero Section -->
    <div class="flex-grow flex items-center justify-center">
      <div class="text-center text-white p-15 bg-[#2E79BA]/30 backdrop-blur-none">
        <div class="flex justify-center items-center space-x-15 mb-6">
          <a href="https://ssvtctesda.com/" target="_blank">
            <img src="../../assets/logo1.png" alt="Logo 1" class="h-40 w-auto transition-transform duration-400 hover:scale-105 hover:brightness-110">
          </a>
          <a href="https://www.tesda.gov.ph/" target="_blank">
            <img src="../../assets/logo2.png" alt="Logo 2" class="h-40 w-auto transition-transform duration-400 hover:scale-105 hover:brightness-110">
          </a>
        </div>
        <h1 class="text-6xl font-bold mb-4">Welcome to SkillsLink</h1>
        <p class="text-2xl mb-6">
          <em>– the official Alumni Tracer System of Simeon Suan Vocational and Technical College (SSVTC).</em> <br>
          Stay connected, update your career journey, and easily request school documents in one platform. <br>
          Together, we build stronger links between the college and its graduates.
        </p>
        <div class="space-x-4">
          <button v-if="!isAuthenticated" class="btn btn-lg text-lg" @click="openLoginModal">
            Get Started
          </button>
          <router-link v-else to="/dashboard" class="btn btn-primary glass">
            Go to Dashboard
          </router-link>
        </div>
      </div>
    </div>

    <!-- Admin Quick Actions (only visible to admins) -->
    <div v-if="isAuthenticated && currentUser?.role === 'admin'" class="fixed bottom-4 right-4">
      <div class="dropdown dropdown-top dropdown-end">
        <div tabindex="0" role="button" class="btn btn-circle btn-primary glass">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </div>
        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52 mb-2">
          <li><router-link to="/users">Manage Users</router-link></li>
        </ul>
      </div>
    </div>

    <!-- Login Modal -->
    <LoginModal 
      :isOpen="isLoginModalOpen"
      @close="closeLoginModal"
      @switch-to-register="switchToRegister"
      @login-success="handleLoginSuccess"
    />

    <!-- Register Modal -->
    <RegisterModal 
      :isOpen="isRegisterModalOpen"
      @close="closeRegisterModal"
      @switch-to-login="switchToLogin"
      @register-success="handleRegisterSuccess"
    />
  </div>
</template>

<script>
import authService from '@/services/auth.js';
import backgroundImage from '@/assets/background.jpg';
import LoginModal from '@/components/modals/LoginModal.vue';
import RegisterModal from '@/components/modals/RegisterModal.vue';

export default {
  name: 'Home',
  components: {
    LoginModal,
    RegisterModal
  },
  data() {
    return {
      backgroundImage,
      isAuthenticated: false,
      currentUser: null,
      isLoginModalOpen: false,
      isRegisterModalOpen: false
    };
  },
  async mounted() {
    await this.checkAuthentication();
  },
  methods: {
    async checkAuthentication() {
      try {
        const authCheck = await authService.checkAuth();
        if (authCheck.loggedIn) {
          this.isAuthenticated = true;
          this.currentUser = authCheck.user || authService.getCurrentUser();
        } else {
          this.isAuthenticated = false;
          this.currentUser = null;
        }
      } catch (error) {
        console.error('Authentication check failed:', error);
        this.isAuthenticated = false;
        this.currentUser = null;
      }
    },
    
    async handleLogout() {
      try {
        const result = await authService.logout();
        // Always update local state after logout attempt
        this.isAuthenticated = false;
        this.currentUser = null;
        
        // Force a re-check of authentication state
        await this.checkAuthentication();
        
        // Redirect to home page after logout
        this.$router.push('/home');
      } catch (error) {
        console.error('Logout failed:', error);
        // Force logout on client side even if server fails
        this.isAuthenticated = false;
        this.currentUser = null;
        localStorage.removeItem('user');
        
        // Redirect to home page even on error
        this.$router.push('/home');
      }
    },
    
    // Login Modal Methods
    openLoginModal() {
      this.isLoginModalOpen = true;
      this.isRegisterModalOpen = false;
    },
    
    closeLoginModal() {
      this.isLoginModalOpen = false;
    },
    
    async handleLoginSuccess() {
      await this.checkAuthentication();
      this.$router.push('/dashboard');
    },
    
    // Register Modal Methods
    openRegisterModal() {
      this.isRegisterModalOpen = true;
      this.isLoginModalOpen = false;
    },
    
    closeRegisterModal() {
      this.isRegisterModalOpen = false;
    },
    
    handleRegisterSuccess() {
      // After successful registration, switch to login
      setTimeout(() => {
        this.switchToLogin();
      }, 2000);
    },
    
    // Modal Switching Methods
    switchToRegister() {
      this.isLoginModalOpen = false;
      this.isRegisterModalOpen = true;
    },
    
    switchToLogin() {
      this.isRegisterModalOpen = false;
      this.isLoginModalOpen = true;
    }
  }
};
</script>

<style scoped>
/* Ensure glass effect works with backdrop blur */
.glass {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}
a .btn {
  background-color: blue;
}
.btn {
  transition: background-color 0.3s ease;
}
.btn:hover {
  background: var(--color-primary-dark); /* Darker navy on hover */
}
</style>

