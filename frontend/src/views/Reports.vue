<template>
  <Layout @logout="handleLogout">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-base-content">Reports & Analytics</h1>
          <p class="text-base-content/70 mt-1">View comprehensive reports and analytics</p>
        </div>
        <button class="btn btn-secondary">
          <IconDownload class="w-4 h-4" />
          Export Report
        </button>
      </div>

      <!-- Reports Content -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">
              <IconPieChart class="w-5 h-5" />
              Alumni Statistics
            </h2>
            <p>Detailed alumni statistics and trends will be displayed here.</p>
          </div>
        </div>

        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">
              <IconTrendingUp class="w-5 h-5" />
              Job Placement Rates
            </h2>
            <p>Job placement analytics and success rates will be shown here.</p>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref } from 'vue'
import Layout from '@/components/Layout.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const handleLogout = () => {
  router.push('/home')
}
</script>