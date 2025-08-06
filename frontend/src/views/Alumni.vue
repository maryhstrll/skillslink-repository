<template>
  <Layout :user-name="currentUser" @logout="handleLogout">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-base-content">Alumni Management</h1>
          <p class="text-base-content/70 mt-1">Manage and track alumni information</p>
        </div>
        <button class="btn btn-primary">
          <i class="fas fa-plus"></i>
          Add Alumni
        </button>
      </div>

      <!-- Alumni Content -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">Alumni Directory</h2>
          <p>Alumni management features will be implemented here.</p>
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
const currentUser = ref('John Admin')

const handleLogout = () => {
  router.push('/login')
}
</script>