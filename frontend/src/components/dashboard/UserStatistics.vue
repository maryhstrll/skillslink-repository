<template>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="stat-card stat-card-primary">
      <div class="stat-figure text-white">
        <i class="fas fa-users text-2xl"></i>
      </div>
      <div class="stat-title text-white/80">Total Users</div>
      <div class="stat-value text-white">{{ userStats.total }}</div>
    </div>
    
    <div class="stat-card stat-card-accent">
      <div class="stat-figure text-white">
        <i class="fas fa-user-check text-2xl"></i>
      </div>
      <div class="stat-title text-white/80">Active Users</div>
      <div class="stat-value text-white">{{ userStats.active }}</div>
    </div>
    
    <div class="stat-card stat-card-ghost">
      <div class="stat-figure">
        <i class="fas fa-user-clock text-2xl"></i>
      </div>
      <div class="stat-title">Pending Users</div>
      <div class="stat-value">{{ userStats.pending }}</div>
    </div>
    
    <div class="stat-card stat-card-secondary">
      <div class="stat-figure text-white">
        <i class="fas fa-user-times text-2xl"></i>
      </div>
      <div class="stat-title text-white/80">Inactive Users</div>
      <div class="stat-value text-white">{{ userStats.inactive }}</div>
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
