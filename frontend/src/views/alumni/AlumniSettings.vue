<template>
  <Layout @logout="handleLogout">
      <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-base-content">Alumni Settings</h1>
            <p class="text-base-content/70 mt-1">Manage your profile and account preferences</p>
          </div>
        </div>

        <!-- Settings Tabs -->
        <div class="tabs tabs-boxed mb-4 settings-tabs">
          <a :class="['tab', activeTab === 'profile' ? 'tab-active' : '']" @click="activeTab = 'profile'">Profile Management</a>
          <a :class="['tab', activeTab === 'archives' ? 'tab-active' : '']" @click="activeTab = 'archives'">Archives</a>
          <a :class="['tab', activeTab === 'general' ? 'tab-active' : '']" @click="activeTab = 'general'">General Settings</a>
        </div>

        <!-- Tab Content -->
        <div v-if="activeTab === 'profile'">
          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="loading loading-spinner loading-lg"></div>
            <span class="ml-2">Loading profile...</span>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="alert alert-error shadow-lg mb-6">
            <div>
              <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <h3 class="font-bold">Unable to Load Profile</h3>
                <div class="text-xs">{{ error }}</div>
                <div class="mt-2">
                  <button @click="$router.push('/home')" class="btn btn-sm btn-primary">
                    Go to Login
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Profile Management Content -->
          <div v-else-if="profile" class="space-y-6">
            <!-- Profile Edit Section -->
            <AlumniProfileEdit :profile="profile" @updated="fetchProfile" />
            
            <!-- Social Links Section -->
            <SocialLinksEdit :profile="profile" @updated="fetchProfile" />
          </div>
        </div>

        <div v-else-if="activeTab === 'archives'">
          <!-- Personal Archives Section -->
          <div class="card bg-base-100 shadow-xl settings-card">
            <div class="card-body">
              <h2 class="card-title">
                <IconArchive class="w-5 h-5" />
                Personal Archives
              </h2>
              <p class="text-base-content/70">View and manage your personal archived data</p>
              
              <div class="space-y-4 mt-4">
                <div class="stats shadow settings-stats">
                  <div class="stat">
                    <div class="stat-title">Archived Forms</div>
                    <div class="stat-value">12</div>
                    <div class="stat-desc">Completed submissions</div>
                  </div>
                  
                  <div class="stat">
                    <div class="stat-title">Documents</div>
                    <div class="stat-value">8</div>
                    <div class="stat-desc">Archived documents</div>
                  </div>
                  
                  <div class="stat">
                    <div class="stat-title">Data Size</div>
                    <div class="stat-value">24MB</div>
                    <div class="stat-desc">Total archived data</div>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="card bg-base-200">
                    <div class="card-body">
                      <h3 class="card-title text-sm">Tracer Form Submissions</h3>
                      <p class="text-xs text-base-content/70">View your historical form submissions</p>
                      <div class="card-actions justify-end">
                        <button class="btn btn-sm btn-outline">View (5)</button>
                      </div>
                    </div>
                  </div>
                  
                  <div class="card bg-base-200">
                    <div class="card-body">
                      <h3 class="card-title text-sm">Document Requests</h3>
                      <p class="text-xs text-base-content/70">Your processed document requests</p>
                      <div class="card-actions justify-end">
                        <button class="btn btn-sm btn-outline">View (3)</button>
                      </div>
                    </div>
                  </div>
                  
                  <div class="card bg-base-200">
                    <div class="card-body">
                      <h3 class="card-title text-sm">Profile Updates</h3>
                      <p class="text-xs text-base-content/70">History of profile changes</p>
                      <div class="card-actions justify-end">
                        <button class="btn btn-sm btn-outline">View (7)</button>
                      </div>
                    </div>
                  </div>
                  
                  <div class="card bg-base-200">
                    <div class="card-body">
                      <h3 class="card-title text-sm">Communication Logs</h3>
                      <p class="text-xs text-base-content/70">Archived messages and notifications</p>
                      <div class="card-actions justify-end">
                        <button class="btn btn-sm btn-outline">View (15)</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="card-actions justify-end mt-6">
                <button class="btn btn-outline">Download All Archives</button>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="activeTab === 'general'">
          <!-- General Settings -->
          <div class="card bg-base-100 shadow-xl settings-card">
            <div class="card-body">
              <h2 class="card-title">
                <IconSettings class="w-5 h-5" />
                General Settings
              </h2>
              <p class="text-base-content/70">Configure your account preferences</p>
              
              <div class="space-y-4 mt-4">
                <div class="form-control">
                  <label class="label cursor-pointer">
                    <span class="label-text">Email Notifications</span>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                  </label>
                </div>
                
                <div class="form-control">
                  <label class="label cursor-pointer">
                    <span class="label-text">Profile Visibility</span>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                  </label>
                </div>
                
                <div class="form-control">
                  <label class="label cursor-pointer">
                    <span class="label-text">Job Opportunity Notifications</span>
                    <input type="checkbox" class="toggle toggle-primary" />
                  </label>
                </div>
              </div>
              
              <div class="card-actions justify-end mt-6">
                <button class="btn btn-primary-custom">Save Settings</button>
              </div>
            </div>
          </div>
        </div>
      </div>
  </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Layout from '@/components/layout/Layout.vue'
import AlumniProfileEdit from '@/components/forms/AlumniProfileEdit.vue'
import SocialLinksEdit from '@/components/forms/SocialLinksEdit.vue'
import { useRouter } from 'vue-router'
import { 
  User as IconUser,
  Settings as IconSettings,
  Archive as IconArchive
} from 'lucide-vue-next'
import alumniService from '@/services/alumni.js'

const router = useRouter()

// State
const activeTab = ref('profile')
const profile = ref(null)
const loading = ref(true)
const error = ref(null)

const handleLogout = () => {
  router.push('/home')
}

const fetchProfile = async () => {
  try {
    loading.value = true
    error.value = null
    
    const profileData = await alumniService.getProfile()
    
    if (profileData && !profileData.error) {
      profile.value = profileData
    } else if (profileData && profileData.error) {
      // Handle specific API errors
      if (profileData.error.includes('Unauthorized')) {
        error.value = 'You must be logged in as an alumni user to access settings.'
      } else if (profileData.error.includes('Access denied')) {
        error.value = 'Access denied. This page is only available to alumni users.'
      } else {
        error.value = profileData.error
      }
    } else {
      error.value = 'Failed to load profile data. Please make sure you are logged in as an alumni user.'
    }
  } catch (err) {
    console.error('Error fetching profile:', err)
    error.value = 'An error occurred while loading your profile'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await fetchProfile()
})
</script>

<style scoped>
h1, p {
  color: var(--color-text-primary);
}
</style>