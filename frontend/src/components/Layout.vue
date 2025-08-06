<template>
  <div class="min-h-screen bg-base-200">
    <!-- Navbar -->
    <div class="navbar bg-base-100 shadow-lg">
      <div class="flex-none lg:hidden">
        <label for="my-drawer" class="btn btn-square btn-ghost">
          <i class="fas fa-bars text-lg"></i>
        </label>
      </div>
      <div class="flex-1">
        <router-link to="/" class="btn btn-ghost text-xl font-bold text-primary">
          SkillsLink
        </router-link>
      </div>
      <div class="flex-none">
        <!-- Notifications -->
        <div class="dropdown dropdown-end">
          <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
            <div class="indicator">
              <i class="fas fa-bell text-lg"></i>
              <span class="badge badge-xs badge-primary indicator-item">3</span>
            </div>
          </div>
          <div tabindex="0" class="dropdown-content card card-compact w-64 bg-base-100 shadow">
            <div class="card-body">
              <span class="font-bold text-lg">3 Notifications</span>
              <span class="text-info">You have new messages</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Drawer -->
    <div class="drawer lg:drawer-open">
      <input id="my-drawer" type="checkbox" class="drawer-toggle" />
      
      <!-- Main Content -->
      <div class="drawer-content">
        <!-- Mobile menu button for larger screens -->
        <div class="navbar lg:hidden bg-base-100">
          <div class="flex-none">
            <label for="my-drawer" class="btn btn-square btn-ghost">
              <i class="fas fa-bars text-lg"></i>
            </label>
          </div>
        </div>
        
        <!-- Page content -->
        <main class="p-4 lg:p-6">
          <slot />
        </main>
      </div>
      
      <!-- Sidebar -->
      <Sidebar 
        :menu-items="menuItems" 
        :user-name="userName"
        @profile="handleProfile"
        @settings="handleSettings" 
        @logout="handleLogout"
      />
    </div>
  </div>
</template>

<script setup>
import Sidebar from './Sidebar.vue'
import authService from '@/services/auth.js'
import { useRouter } from 'vue-router'

const props = defineProps({
  userName: {
    type: String,
    default: 'Admin User'
  }
})

const emit = defineEmits(['profile', 'settings', 'logout'])
const router = useRouter()

// Define menu items
const menuItems = [
  {
    path: '/dashboard',
    label: 'Dashboard',
    icon: 'fas fa-home'
  },
  {
    path: '/alumni',
    label: 'Alumni',
    icon: 'fas fa-users'
  },
  {
    path: '/reports',
    label: 'Reports',
    icon: 'fas fa-chart-bar'
  },
  {
    path: '/users',
    label: 'Users',
    icon: 'fas fa-user-cog'
  },
  {
    path: '/settings',
    label: 'Settings',
    icon: 'fas fa-cog'
  }
]

const handleProfile = () => {
  emit('profile')
  console.log('Profile clicked')
}

const handleSettings = () => {
  emit('settings')
  router.push('/settings')
}

const handleLogout = async () => {
  try {
    const result = await authService.logout()
    if (result.success) {
      emit('logout')
      router.push('/login')
    } else {
      console.error('Logout failed:', result.error)
      // Still redirect to login even if API fails
      router.push('/login')
    }
  } catch (error) {
    console.error('Logout error:', error)
    // Still redirect to login even if there's an error
    router.push('/login')
  }
}
</script>
