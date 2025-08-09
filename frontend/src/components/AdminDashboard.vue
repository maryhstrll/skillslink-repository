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
        <div class="stat bg-base-100 rounded-lg shadow">
          <div class="stat-figure text-primary">
            <i class="fas fa-users text-3xl"></i>
          </div>
          <div class="stat-title">Total Alumni</div>
          <div class="stat-value text-primary">{{ stats.totalAlumni }}</div>
          <div class="stat-desc">↗︎ 400 (22%)</div>
        </div>
        
        <div class="stat bg-base-100 rounded-lg shadow">
          <div class="stat-figure text-secondary">
            <i class="fas fa-briefcase text-3xl"></i>
          </div>
          <div class="stat-title">Active Jobs</div>
          <div class="stat-value text-secondary">{{ stats.activeJobs }}</div>
          <div class="stat-desc">↗︎ 40 (2%)</div>
        </div>
        
        <div class="stat bg-base-100 rounded-lg shadow">
          <div class="stat-figure text-accent">
            <i class="fas fa-chart-line text-3xl"></i>
          </div>
          <div class="stat-title">Success Rate</div>
          <div class="stat-value text-accent">{{ stats.successRate }}%</div>
          <div class="stat-desc">↗︎ 90 (14%)</div>
        </div>
        
        <div class="stat bg-base-100 rounded-lg shadow">
          <div class="stat-figure text-info">
            <i class="fas fa-bell text-3xl"></i>
          </div>
          <div class="stat-title">Notifications</div>
          <div class="stat-value text-info">{{ stats.notifications }}</div>
          <div class="stat-desc">3 new messages</div>
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
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Dashboard stats
const stats = ref({
  totalAlumni: 1259,
  activeJobs: 186,
  successRate: 86,
  notifications: 12
})

// Recent activities
const recentActivities = ref([
  {
    id: 1,
    user: 'Sarah Johnson',
    userInitials: 'SJ',
    action: 'Updated profile',
    time: '2 minutes ago',
    status: 'Completed',
    statusClass: 'badge-success'
  },
  {
    id: 2,
    user: 'Mike Chen',
    userInitials: 'MC',
    action: 'Applied for job',
    time: '15 minutes ago',
    status: 'Pending',
    statusClass: 'badge-warning'
  },
  {
    id: 3,
    user: 'Emily Davis',
    userInitials: 'ED',
    action: 'Logged in',
    time: '1 hour ago',
    status: 'Active',
    statusClass: 'badge-info'
  }
])

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