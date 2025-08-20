<template>
    <Layout @logout="handleLogout">
  <div class="container mx-auto p-6 max-w-4xl">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-base-content">Alumni Settings</h1>
          <p class="text-base-content/70 mt-1">Manage your account preferences and settings</p>
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

      <!-- Placeholder for future settings -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">
            <i class="fas fa-cog"></i>
            More Settings Coming Soon
          </h2>
          <p class="text-base-content/70">
            Additional alumni settings like notifications, privacy preferences, and profile visibility will be available soon.
          </p>
        </div>
      </div>
    </div>
  </div>
    </Layout>
</template>

<script setup>
import { ref } from 'vue'
import Layout from '@/components/Layout.vue'
import ThemeToggle from '@/components/ThemeToggle.vue'
import { useRouter } from 'vue-router'

const router = useRouter();

// State
const showSaveMessage = ref(false)

// Methods
const onThemeChanged = (themeData) => {
  console.log('Alumni theme changed:', themeData)
  
  // Show save message
  showSaveMessage.value = true
  
  // Hide message after 3 seconds
  setTimeout(() => {
    showSaveMessage.value = false
  }, 3000)
  
  // Here you can also save to backend if needed
  // await saveAlumniThemePreference(themeData)
}

const handleLogout = () => {
  router.push('/home')
}
</script>
