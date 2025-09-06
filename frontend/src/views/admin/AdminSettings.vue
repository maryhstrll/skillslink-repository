<template>
  <Layout @logout="handleLogout">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-base-content">Admin Settings</h1>
          <p class="text-base-content/70 mt-1">Configure application settings and preferences</p>
        </div>
      </div>

      <!-- Theme Settings -->
      <ThemeToggle @theme-changed="onThemeChanged" />

      <!-- Save Notification -->
      <div v-if="showSaveMessage" class="alert alert-success">
        <div>
          <i class="fas fa-check-circle"></i>
          <span>Theme settings saved successfully!</span>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref } from 'vue'
import Layout from '@/components/layout/Layout.vue'
import ThemeToggle from '@/components/ui/ThemeToggle.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// State
const showSaveMessage = ref(false)

// Methods
const onThemeChanged = (themeData) => {
  console.log('Theme changed:', themeData)
  
  // Show save message
  showSaveMessage.value = true
  
  // Hide message after 3 seconds
  setTimeout(() => {
    showSaveMessage.value = false
  }, 3000)
  
  // Here you can also save to backend if needed
  // await saveThemePreference(themeData)
}

const handleLogout = () => {
  router.push('/home')
}
</script>