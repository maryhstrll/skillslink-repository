<template>
  <div class="space-y-6">
    <!-- Welcome Section -->
    <div class="hero bg-secondary text-secondary-content rounded-lg">
      <div class="hero-content text-center">
        <div class="max-w-md">
          <h1 class="mb-5 text-3xl font-bold">Welcome to Staff Dashboard</h1>
          <p class="mb-5">Manage alumni records and view analytics with your staff access.</p>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="stat bg-base-100 rounded-lg shadow">
        <div class="stat-figure text-primary">
          <i class="fas fa-users text-3xl"></i>
        </div>
        <div class="stat-title">Assigned Alumni</div>
        <div class="stat-value text-primary">{{ stats.assignedAlumni }}</div>
        <div class="stat-desc">Alumni under your management</div>
      </div>
      
      <div class="stat bg-base-100 rounded-lg shadow">
        <div class="stat-figure text-secondary">
          <i class="fas fa-file-alt text-3xl"></i>
        </div>
        <div class="stat-title">Tracer Forms</div>
        <div class="stat-value text-secondary">{{ stats.tracerForms }}</div>
        <div class="stat-desc">Forms processed this month</div>
      </div>
      
      <div class="stat bg-base-100 rounded-lg shadow">
        <div class="stat-figure text-accent">
          <i class="fas fa-chart-line text-3xl"></i>
        </div>
        <div class="stat-title">Response Rate</div>
        <div class="stat-value text-accent">{{ stats.responseRate }}%</div>
        <div class="stat-desc">Alumni response rate</div>
      </div>
      
      <div class="stat bg-base-100 rounded-lg shadow">
        <div class="stat-figure text-info">
          <i class="fas fa-tasks text-3xl"></i>
        </div>
        <div class="stat-title">Pending Tasks</div>
        <div class="stat-value text-info">{{ stats.pendingTasks }}</div>
        <div class="stat-desc">Items requiring attention</div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Recent Alumni Updates -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">
            <i class="fas fa-user-graduate"></i>
            Recent Alumni Updates
          </h2>
          <div class="space-y-3">
            <div v-for="update in recentUpdates" :key="update.id" class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                  <div class="bg-neutral text-neutral-content rounded-full w-8">
                    <span class="text-xs">{{ update.initials }}</span>
                  </div>
                </div>
                <div>
                  <p class="font-medium">{{ update.name }}</p>
                  <p class="text-sm text-base-content/70">{{ update.action }}</p>
                </div>
              </div>
              <div class="text-sm text-base-content/50">{{ update.time }}</div>
            </div>
          </div>
          <div class="card-actions justify-end mt-4">
            <button class="btn btn-sm btn-primary" @click="router.push('/alumni')">
              View All Alumni
            </button>
          </div>
        </div>
      </div>

      <!-- Task Summary -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">
            <i class="fas fa-clipboard-check"></i>
            Task Summary
          </h2>
          <div class="space-y-3">
            <div v-for="task in tasks" :key="task.id" class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
              <div class="flex items-center gap-3">
                <i :class="[task.icon, 'text-lg', task.iconColor]"></i>
                <div>
                  <p class="font-medium">{{ task.title }}</p>
                  <p class="text-sm text-base-content/70">{{ task.description }}</p>
                </div>
              </div>
              <div class="badge" :class="task.priorityClass">{{ task.priority }}</div>
            </div>
          </div>
          <div class="card-actions justify-end mt-4">
            <button class="btn btn-sm btn-secondary" @click="router.push('/reports')">
              View Reports
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Access Buttons -->
    <div class="card bg-base-100 shadow-xl">
      <div class="card-body">
        <h2 class="card-title">
          <i class="fas fa-bolt"></i>
          Quick Actions
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <button class="btn btn-outline btn-primary" @click="router.push('/alumni')">
            <i class="fas fa-plus"></i>
            Manage Alumni
          </button>
          <button class="btn btn-outline btn-secondary" @click="router.push('/reports')">
            <i class="fas fa-download"></i>
            Download Reports
          </button>
          <button class="btn btn-outline btn-accent">
            <i class="fas fa-envelope"></i>
            Send Notifications
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Dashboard stats (staff-specific)
const stats = ref({
  assignedAlumni: 156,
  tracerForms: 45,
  responseRate: 78,
  pendingTasks: 8
})

// Recent alumni updates
const recentUpdates = ref([
  {
    id: 1,
    name: 'Maria Santos',
    initials: 'MS',
    action: 'Updated employment status',
    time: '1 hour ago'
  },
  {
    id: 2,
    name: 'John Doe',
    initials: 'JD',
    action: 'Completed tracer form',
    time: '3 hours ago'
  },
  {
    id: 3,
    name: 'Jane Smith',
    initials: 'JS',
    action: 'Updated contact information',
    time: '5 hours ago'
  }
])

// Staff tasks
const tasks = ref([
  {
    id: 1,
    title: 'Review Alumni Profiles',
    description: 'Verify recent profile updates',
    icon: 'fas fa-user-check',
    iconColor: 'text-primary',
    priority: 'High',
    priorityClass: 'badge-error'
  },
  {
    id: 2,
    title: 'Process Tracer Forms',
    description: 'Review submitted forms',
    icon: 'fas fa-file-invoice',
    iconColor: 'text-secondary',
    priority: 'Medium',
    priorityClass: 'badge-warning'
  },
  {
    id: 3,
    title: 'Generate Monthly Report',
    description: 'Compile analytics data',
    icon: 'fas fa-chart-pie',
    iconColor: 'text-accent',
    priority: 'Low',
    priorityClass: 'badge-info'
  }
])
</script>
