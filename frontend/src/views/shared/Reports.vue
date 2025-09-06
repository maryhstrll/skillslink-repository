<template>
  <Layout @logout="handleLogout">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold text-base-content">Reports Center</h1>
          <p class="text-base-content/70 mt-1">
            Access various reports and analytics for the SkillsLink platform
          </p>
        </div>
        <div class="flex gap-2">
          <button class="btn btn-outline" @click="refreshData">
            <IconRefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
            Refresh
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="loading loading-spinner loading-lg"></div>
        <span class="ml-4 text-lg">Loading reports...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="alert alert-error">
        <IconX class="w-6 h-6" />
        <div>
          <h3 class="font-bold">Error Loading Reports</h3>
          <div class="text-sm">{{ error }}</div>
        </div>
        <button class="btn btn-sm" @click="refreshData">Retry</button>
      </div>

      <!-- Reports Content -->
      <div v-else class="space-y-6">
        <!-- Report Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Employment Dashboard -->
          <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
            <div class="card-body">
              <h2 class="card-title flex items-center">
                <IconBarChart3 class="w-6 h-6" style="color: var(--color-primary);" />
                Employment Dashboard
              </h2>
              <p class="text-base-content/70">
                Comprehensive employment analytics, salary trends, and job-course alignment metrics.
              </p>
              <div class="card-actions justify-end">
                <button 
                  class="btn-primary-custom btn"
                  @click="router.push('/admin_dashboard')"
                >
                  View Dashboard
                </button>
              </div>
            </div>
          </div>

          <!-- Alumni Reports -->
          <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
            <div class="card-body">
              <h2 class="card-title flex items-center">
                <IconUsers class="w-6 h-6" style="color: var(--color-accent);" />
                Alumni Reports
              </h2>
              <p class="text-base-content/70">
                Detailed alumni profiles, graduation statistics, and demographic analysis.
              </p>
              <div class="card-actions justify-end">
                <button 
                  class="btn-accent-custom btn"
                  @click="router.push('/admin_alumni')"
                >
                  View Reports
                </button>
              </div>
            </div>
          </div>

          <!-- Form Responses -->
          <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
            <div class="card-body">
              <h2 class="card-title flex items-center">
                <IconFileText class="w-6 h-6" style="color: var(--color-ghost);" />
                Form Responses
              </h2>
              <p class="text-base-content/70">
                Tracer study responses, survey data, and feedback analysis.
              </p>
              <div class="card-actions justify-end">
                <button 
                  class="btn-ghost-custom btn"
                  @click="router.push('/admin_form_responses')"
                >
                  View Forms
                </button>
              </div>
            </div>
          </div>

          <!-- Document Requests -->
          <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
            <div class="card-body">
              <h2 class="card-title flex items-center">
                <IconFileText class="w-6 h-6" style="color: var(--color-ghost);" />
                Document Requests
              </h2>
              <p class="text-base-content/70">
                Track and manage alumni document requests and processing status.
              </p>
              <div class="card-actions justify-end">
                <button 
                  class="btn-ghost-custom btn"
                  @click="router.push('/admin_document_requests')"
                >
                  Manage Requests
                </button>
              </div>
            </div>
          </div>

          <!-- User Approvals -->
          <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
            <div class="card-body">
              <h2 class="card-title flex items-center">
                <IconUserCheck class="w-6 h-6" style="color: var(--color-secondary);" />
                User Approvals
              </h2>
              <p class="text-base-content/70">
                Review and approve pending user registrations and profile updates.
              </p>
              <div class="card-actions justify-end">
                <button 
                  class="btn-secondary-custom btn"
                  @click="router.push('/admin_user_approvals')"
                >
                  Review Approvals
                </button>
              </div>
            </div>
          </div>

          <!-- System Reports -->
          <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
            <div class="card-body">
              <h2 class="card-title flex items-center">
                <IconSettings class="w-6 h-6 text-accent" />
                System Reports
              </h2>
              <p class="text-base-content/70">
                System usage statistics, performance metrics, and audit logs.
              </p>
              <div class="card-actions justify-end">
                <button 
                  class="btn btn-accent"
                  disabled
                >
                  Coming Soon
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Stats Overview -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">
              <IconPieChart class="w-6 h-6" />
              Quick Overview
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
              <div class="stat">
                <div class="stat-title">Total Reports Generated</div>
                <div class="stat-value text-primary">{{ quickStats.totalReports }}</div>
                <div class="stat-desc">This month</div>
              </div>
              <div class="stat">
                <div class="stat-title">Active Users</div>
                <div class="stat-value text-secondary">{{ quickStats.activeUsers }}</div>
                <div class="stat-desc">Last 30 days</div>
              </div>
              <div class="stat">
                <div class="stat-title">Data Accuracy</div>
                <div class="stat-value text-success">{{ quickStats.dataAccuracy }}%</div>
                <div class="stat-desc">Quality score</div>
              </div>
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
import { useRouter } from 'vue-router'
// Icons are globally registered in main.js - no need to import

const router = useRouter()

// Reactive state
const loading = ref(false)
const error = ref(null)

// Quick stats for the overview
const quickStats = ref({
  totalReports: 45,
  activeUsers: 128,
  dataAccuracy: 95
})

const refreshData = async () => {
  try {
    loading.value = true
    error.value = null
    // Add any refresh logic here
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate loading
  } catch (err) {
    error.value = 'Failed to refresh data'
  } finally {
    loading.value = false
  }
}

const handleLogout = () => {
  router.push('/home')
}

// Initialize on mount
onMounted(() => {
  // Any initialization logic
})
</script>
