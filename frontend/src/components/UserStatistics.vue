<template>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="stat bg-base-100 rounded-lg shadow">
      <div class="stat-figure text-primary">
        <i class="fas fa-users text-2xl"></i>
      </div>
      <div class="stat-title">Total Users</div>
      <div class="stat-value text-primary">{{ userStats.total }}</div>
    </div>
    
    <div class="stat bg-base-100 rounded-lg shadow">
      <div class="stat-figure text-success">
        <i class="fas fa-user-check text-2xl"></i>
      </div>
      <div class="stat-title">Active Users</div>
      <div class="stat-value text-success">{{ userStats.active }}</div>
    </div>
    
    <div class="stat bg-base-100 rounded-lg shadow">
      <div class="stat-figure text-warning">
        <i class="fas fa-user-clock text-2xl"></i>
      </div>
      <div class="stat-title">Pending Users</div>
      <div class="stat-value text-warning">{{ userStats.pending }}</div>
    </div>
    
    <div class="stat bg-base-100 rounded-lg shadow">
      <div class="stat-figure text-error">
        <i class="fas fa-user-times text-2xl"></i>
      </div>
      <div class="stat-title">Inactive Users</div>
      <div class="stat-value text-error">{{ userStats.inactive }}</div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  userStats: {
    type: Object,
    required: true,
    default: () => ({
      total: 0,
      active: 0,
      pending: 0,
      inactive: 0
    })
  }
})
</script>
