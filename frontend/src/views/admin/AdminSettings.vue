<template>
  <Layout @logout="handleLogout">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-base-content">Admin Settings</h1>
          <p class="text-base-content/70 mt-1">Manage your profile, activity, and application settings</p>
        </div>
      </div>

      <!-- Settings Tabs -->
      <div class="tabs tabs-boxed mb-4 settings-tabs">
        <a :class="['tab', activeTab === 'profile' ? 'tab-active' : '']" @click="activeTab = 'profile'">Profile Management</a>
        <a :class="['tab', activeTab === 'activity' ? 'tab-active' : '']" @click="activeTab = 'activity'">Recent Activity</a>
        <a :class="['tab', activeTab === 'archives' ? 'tab-active' : '']" @click="activeTab = 'archives'">Archives</a>
        <a :class="['tab', activeTab === 'general' ? 'tab-active' : '']" @click="activeTab = 'general'">General Settings</a>
      </div>

      <!-- Tab Content -->
      <div v-if="activeTab === 'profile'">
        <!-- Profile Management Section -->
        <div class="card bg-base-100 shadow-xl settings-card">
          <div class="card-body">
            <h2 class="card-title">
              <IconUser class="w-5 h-5" />
              Profile Management
            </h2>
            <p class="text-base-content/70 mb-4">Manage your administrator profile information</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-4">
                <div class="form-control">
                  <label class="label">
                    <span class="label-text">Display Name</span>
                  </label>
                  <input type="text" placeholder="Admin User" class="input input-bordered" />
                </div>
                <div class="form-control">
                  <label class="label">
                    <span class="label-text">Email</span>
                  </label>
                  <input type="email" placeholder="admin@example.com" class="input input-bordered" />
                </div>
                <div class="form-control">
                  <label class="label">
                    <span class="label-text">Role</span>
                  </label>
                  <input type="text" value="System Administrator" class="input input-bordered" disabled />
                </div>
              </div>
              
              <div class="space-y-4">
                <div class="form-control">
                  <label class="label">
                    <span class="label-text">Department</span>
                  </label>
                  <input type="text" placeholder="IT Department" class="input input-bordered" />
                </div>
                <div class="form-control">
                  <label class="label">
                    <span class="label-text">Phone Number</span>
                  </label>
                  <input type="tel" placeholder="+63 123 456 7890" class="input input-bordered" />
                </div>
                <div class="form-control">
                  <label class="label">
                    <span class="label-text">Status</span>
                  </label>
                  <div class="badge badge-success">Active</div>
                </div>
              </div>
            </div>
            
            <div class="card-actions justify-end mt-6">
              <button class="btn btn-primary-custom">Update Profile</button>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="activeTab === 'activity'">
        <!-- Recent Activity Section -->
        <RecentActivity />
      </div>

      <div v-else-if="activeTab === 'archives'">
        <!-- Archives Section -->
        <div class="card bg-base-100 shadow-xl settings-card">
          <div class="card-body">
            <h2 class="card-title">
              <IconArchive class="w-5 h-5" />
              Archives Management
            </h2>
            <p class="text-base-content/70">Manage archived data and historical records</p>
            
            <div class="space-y-4 mt-4">
              <div class="stats shadow settings-stats">
                <div class="stat">
                  <div class="stat-title">Archived Users</div>
                  <div class="stat-value">89</div>
                  <div class="stat-desc">↗︎ 4% increase from last month</div>
                </div>
                
                <div class="stat">
                  <div class="stat-title">Archived Forms</div>
                  <div class="stat-value">156</div>
                  <div class="stat-desc">↗︎ 12% increase from last month</div>
                </div>
                
                <div class="stat">
                  <div class="stat-title">Storage Used</div>
                  <div class="stat-value">2.4GB</div>
                  <div class="stat-desc">↗︎ 8% increase from last month</div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                  <label class="label cursor-pointer">
                    <span class="label-text">Auto-archive inactive users after 2 years</span>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                  </label>
                </div>
                
                <div class="form-control">
                  <label class="label cursor-pointer">
                    <span class="label-text">Auto-archive completed forms after 5 years</span>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                  </label>
                </div>
                
                <div class="form-control">
                  <label class="label cursor-pointer">
                    <span class="label-text">Compress archived data</span>
                    <input type="checkbox" class="toggle toggle-primary" />
                  </label>
                </div>
                
                <div class="form-control">
                  <label class="label cursor-pointer">
                    <span class="label-text">Backup archives to external storage</span>
                    <input type="checkbox" class="toggle toggle-primary" />
                  </label>
                </div>
              </div>
            </div>
            
            <div class="card-actions justify-between mt-6">
              <div class="join">
                <button class="btn btn-outline join-item">Export Archives</button>
                <button class="btn btn-outline join-item">Import Archives</button>
              </div>
              <button class="btn btn-primary-custom">Save Archive Settings</button>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="activeTab === 'general'">
        <!-- General Settings -->
        <div class="card bg-base-100 shadow-xl settings-card">
          <div class="card-body">
            <h2 class="card-title">
              <IconSettings class="w-5 h-5" />
              General Settings
            </h2>
            <p class="text-base-content/70">Configure application preferences and system settings</p>
            
            <div class="space-y-4 mt-4">
              <div class="form-control">
                <label class="label cursor-pointer">
                  <span class="label-text">Email Notifications</span>
                  <input type="checkbox" class="toggle toggle-primary" checked />
                </label>
              </div>
              
              <div class="form-control">
                <label class="label cursor-pointer">
                  <span class="label-text">System Alerts</span>
                  <input type="checkbox" class="toggle toggle-primary" checked />
                </label>
              </div>
              
              <div class="form-control">
                <label class="label cursor-pointer">
                  <span class="label-text">Auto-logout after inactivity</span>
                  <input type="checkbox" class="toggle toggle-primary" />
                </label>
              </div>
            </div>
            
            <div class="card-actions justify-end mt-6">
              <button class="btn btn-primary-custom">Save Settings</button>
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
import RecentActivity from '@/components/dashboard/RecentActivity.vue'
import { useRouter, useRoute } from 'vue-router'
import { 
  User as IconUser,
  Settings as IconSettings,
  Archive as IconArchive
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()

// State
const activeTab = ref('profile')

// Check URL parameters for initial tab
onMounted(() => {
  const tab = route.query.tab
  if (tab && ['profile', 'activity', 'archives', 'general'].includes(tab)) {
    activeTab.value = tab
  }
})

const handleLogout = () => {
  router.push('/home')
}
</script>

<style scoped>
h1, p {
  color: var(--color-text-primary);
}
</style>