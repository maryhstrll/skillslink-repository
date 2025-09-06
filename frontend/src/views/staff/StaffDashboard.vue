<template>
  <Layout>
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold">Staff Dashboard</h1>
        <div class="text-sm text-gray-600">
          Welcome back, Staff Member
        </div>
      </div>
      
      <StaffDashboardComponent />
    </div>
  </Layout>
</template>

<script setup>
import Layout from '@/components/layout/Layout.vue'
import StaffDashboardComponent from '@/components/dashboard/StaffDashboard.vue'
</script>
