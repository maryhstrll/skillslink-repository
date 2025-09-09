<template>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <StatCard
      title="Total Users"
      :value="userStats.total"
      description="Registered users in the system"
      :icon="Users"
      variant="primary"
    />
    
    <StatCard
      title="Active Users"
      :value="userStats.active"
      description="Currently active users"
      :icon="UserCheck"
      variant="accent"
    />
    
    <StatCard
      title="Pending Users"
      :value="userStats.pending"
      description="Awaiting approval"
      :icon="User"
      variant="ghost"
    />
    
    <StatCard
      title="Inactive Users"
      :value="userStats.inactive"
      description="Users with inactive status"
      :icon="X"
      variant="secondary"
    />
  </div>
</template>

<script setup>
import StatCard from '@/components/cards/StatCard.vue'
import { 
  Users,
  UserCheck,
  User,
  X
} from 'lucide-vue-next'

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
