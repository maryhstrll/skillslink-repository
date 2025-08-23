<template>
        <div class="space-y-6">
      <!-- Welcome Section -->
      <div class="hero bg-primary text-primary-content rounded-lg">
        <div class="hero-content text-center">
          <div class="max-w-md">
            <h1 class="mb-5 text-3xl font-bold">Welcome to Admin Dashboard</h1>
            <p class="mb-5">Manage your SkillsLink platform efficiently with our comprehensive admin tools.</p>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-lg shadow relative">
          <div class="absolute top-4 right-4">
            <i class="fas fa-users text-3xl text-primary"></i>
          </div>
          <div class="stat-title">Total Alumni</div>
          <div class="stat-value text-primary">
            <span v-if="loading" class="loading loading-spinner loading-sm"></span>
            <span v-else>{{ stats.totalAlumni }}</span>
          </div>
          <div class="stat-desc">Registered alumni in system</div>
        </div>
        
        <div class="stat bg-base-100 rounded-lg shadow relative">
          <div class="absolute top-4 right-4">
            <i class="fas fa-briefcase text-3xl text-secondary"></i>
          </div>
          <div class="stat-title">Active Jobs</div>
          <div class="stat-value text-secondary">{{ stats.activeJobs }}</div>
          <div class="stat-desc">↗︎ 00 (0%)</div>
        </div>
        
        <div class="stat bg-base-100 rounded-lg shadow relative">
          <div class="absolute top-4 right-4">
            <i class="fas fa-chart-line text-3xl text-accent"></i>
          </div>
          <div class="stat-title">Success Rate</div>
          <div class="stat-value text-accent">{{ stats.successRate }}%</div>
          <div class="stat-desc">↗︎ 00 (0%)</div>
        </div>
        
        <div class="stat bg-base-100 rounded-lg shadow relative">
          <div class="absolute top-4 right-4">
            <i class="fas fa-bell text-3xl text-info"></i>
          </div>
          <div class="stat-title">Notifications</div>
          <div class="stat-value text-info">{{ stats.notifications }}</div>
          <div class="stat-desc">0 messages</div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">
            <i class="fas fa-clock"></i>
            Recent Activity
          </h2>
          <div class="overflow-x-auto">
            <table class="table table-zebra">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Action</th>
                  <th>Time</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="activity in recentActivities" :key="activity.id">
                  <td>
                    <div class="flex items-center gap-3">
                      <div class="avatar placeholder">
                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                          <span class="text-xs">{{ activity.userInitials }}</span>
                        </div>
                      </div>
                      <span>{{ activity.user }}</span>
                    </div>
                  </td>
                  <td>{{ activity.action }}</td>
                  <td>{{ activity.time }}</td>
                  <td>
                    <div class="badge" :class="activity.statusClass">{{ activity.status }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Document Requests Management -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">
            <i class="fas fa-file-alt"></i>
            Document Requests
          </h2>
          <p class="text-base-content/70 mb-4">
            Manage and process alumni document requests for transcripts, diplomas, and certificates.
          </p>
          <div class="card-actions justify-end">
            <button 
              class="btn btn-primary"
              @click="router.push('/admin_document_requests')"
            >
              <i class="fas fa-cogs mr-2"></i>
              Manage Requests
            </button>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import alumniService from '@/services/alumni.js'

const router = useRouter()

// Dashboard stats
const stats = ref({
  totalAlumni: 0,
  activeJobs: 0,
  successRate: 0,
  notifications: 0
})

const loading = ref(true)

// Recent activities
const recentActivities = ref([])

// Fetch alumni data and update stats
const fetchAlumniStats = async () => {
  try {
    loading.value = true
    const alumniData = await alumniService.getAll()
    stats.value.totalAlumni = alumniData.length
  } catch (error) {
    console.error('Error fetching alumni stats:', error)
    stats.value.totalAlumni = 0
  } finally {
    loading.value = false
  }
}

// Initialize data on component mount
onMounted(() => {
  fetchAlumniStats()
})

// Event handlers
const handleLogout = () => {
  // Add logout logic
  console.log('Logging out...')
  router.push('/home')
}

const handleProfile = () => {
  console.log('Opening profile...')
  // Add profile logic
}

const handleSettings = () => {
  console.log('Opening settings...')
  router.push('/settings')
}
</script>