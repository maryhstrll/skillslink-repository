<template>
  <Layout>
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold">Admin Dashboard</h1>
        <div class="text-sm text-gray-600">
          Welcome back, Administrator
        </div>
      </div>
      
      <AdminDashboardComponent />
    </div>
  </Layout>
</template>

<script setup>
import Layout from '@/components/Layout.vue'
import AdminDashboardComponent from '@/components/AdminDashboard.vue'
</script>
