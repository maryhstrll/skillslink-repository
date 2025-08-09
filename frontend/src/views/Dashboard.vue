<template>
  <Layout>
    <component :is="dashboardComponent" />
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Layout from '@/components/Layout.vue'
import AdminDashboard from '@/components/AdminDashboard.vue'
import AlumniDashboard from '@/components/AlumniDashboard.vue'
import StaffDashboard from '@/components/StaffDashboard.vue'
import authService from '@/services/auth.js'

const role = ref('alumni') // Default to alumni for safety
const currentUser = ref(null)

// Get user role from localStorage or auth service
const getUserRole = () => {
  try {
    const user = authService.getCurrentUser()
    if (user && user.role) {
      return user.role
    }
    
    // Fallback: try to get from localStorage 'user' object
    const userStr = localStorage.getItem('user')
    if (userStr) {
      const userData = JSON.parse(userStr)
      return userData.role || 'alumni'
    }
  } catch (error) {
    console.error('Error getting user role:', error)
  }
  return 'alumni' // Safe default
}

onMounted(async () => {
  try {
    // Check authentication and get user data
    const authCheck = await authService.checkAuth()
    if (authCheck.loggedIn && authCheck.user) {
      currentUser.value = authCheck.user
      role.value = authCheck.user.role || 'alumni'
    } else {
      // Fallback to localStorage
      role.value = getUserRole()
    }
  } catch (error) {
    console.error('Failed to check authentication:', error)
    role.value = getUserRole()
  }
})

const dashboardComponent = computed(() => {
  if (role.value === 'admin') return AdminDashboard
  if (role.value === 'staff') return StaffDashboard
  if (role.value === 'alumni') return AlumniDashboard
  return AlumniDashboard // Default to alumni dashboard for safety
})
</script>
