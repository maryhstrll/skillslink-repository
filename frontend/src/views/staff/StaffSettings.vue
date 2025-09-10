<template>
  <Layout @logout="handleLogout">
    <div class="container mx-auto p-6 max-w-4xl">
      <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-base-content">Staff Settings</h1>
            <p class="text-base-content/70 mt-1">Manage your profile and staff preferences</p>
          </div>
        </div>

        <!-- Settings Tabs -->
        <div class="tabs tabs-boxed mb-4 settings-tabs">
          <a :class="['tab', activeTab === 'profile' ? 'tab-active' : '']" @click="activeTab = 'profile'">Profile Management</a>
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
              <p class="text-base-content/70 mb-4">Manage your staff profile information</p>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text">Full Name</span>
                    </label>
                    <input type="text" placeholder="Jane Smith" class="input input-bordered" />
                  </div>
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text">Email</span>
                    </label>
                    <input type="email" placeholder="jane.smith@example.com" class="input input-bordered" />
                  </div>
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text">Employee ID</span>
                    </label>
                    <input type="text" value="STF-2024-001" class="input input-bordered" disabled />
                  </div>
                </div>
                
                <div class="space-y-4">
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text">Department</span>
                    </label>
                    <input type="text" placeholder="Alumni Relations" class="input input-bordered" />
                  </div>
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text">Position</span>
                    </label>
                    <input type="text" placeholder="Alumni Coordinator" class="input input-bordered" />
                  </div>
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text">Phone Number</span>
                    </label>
                    <input type="tel" placeholder="+63 123 456 7890" class="input input-bordered" />
                  </div>
                </div>
              </div>
              
              <div class="card-actions justify-end mt-6">
                <button class="btn btn-primary-custom">Update Profile</button>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="activeTab === 'archives'">
          <!-- Staff Archives Section -->
          <div class="card bg-base-100 shadow-xl settings-card">
            <div class="card-body">
              <h2 class="card-title">
                <IconArchive class="w-5 h-5" />
                Staff Archives
              </h2>
              <p class="text-base-content/70">View and manage your staff-related archived data</p>
              
              <div class="space-y-4 mt-4">
                <div class="stats shadow settings-stats">
                  <div class="stat">
                    <div class="stat-title">Processed Reports</div>
                    <div class="stat-value">45</div>
                    <div class="stat-desc">Generated reports</div>
                  </div>
                  
                  <div class="stat">
                    <div class="stat-title">Alumni Assisted</div>
                    <div class="stat-value">128</div>
                    <div class="stat-desc">Total interactions</div>
                  </div>
                  
                  <div class="stat">
                    <div class="stat-title">Data Processed</div>
                    <div class="stat-value">156MB</div>
                    <div class="stat-desc">Total data handled</div>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="card bg-base-200">
                    <div class="card-body">
                      <h3 class="card-title text-sm">Report History</h3>
                      <p class="text-xs text-base-content/70">Your generated reports and analytics</p>
                      <div class="card-actions justify-end">
                        <button class="btn btn-sm btn-outline">View (45)</button>
                      </div>
                    </div>
                  </div>
                  
                  <div class="card bg-base-200">
                    <div class="card-body">
                      <h3 class="card-title text-sm">Alumni Management</h3>
                      <p class="text-xs text-base-content/70">Alumni records you've managed</p>
                      <div class="card-actions justify-end">
                        <button class="btn btn-sm btn-outline">View (128)</button>
                      </div>
                    </div>
                  </div>
                  
                  <div class="card bg-base-200">
                    <div class="card-body">
                      <h3 class="card-title text-sm">Document Processing</h3>
                      <p class="text-xs text-base-content/70">Document requests processed</p>
                      <div class="card-actions justify-end">
                        <button class="btn btn-sm btn-outline">View (89)</button>
                      </div>
                    </div>
                  </div>
                  
                  <div class="card bg-base-200">
                    <div class="card-body">
                      <h3 class="card-title text-sm">Activity Logs</h3>
                      <p class="text-xs text-base-content/70">System activity history</p>
                      <div class="card-actions justify-end">
                        <button class="btn btn-sm btn-outline">View (234)</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="card-actions justify-end mt-6">
                <button class="btn btn-outline">Download Staff Archives</button>
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
              <p class="text-base-content/70">Configure your staff account preferences</p>
              
              <div class="space-y-4 mt-4">
                <div class="form-control">
                  <label class="label cursor-pointer">
                    <span class="label-text">Email Notifications</span>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                  </label>
                </div>
                
                <div class="form-control">
                  <label class="label cursor-pointer">
                    <span class="label-text">Report Notifications</span>
                    <input type="checkbox" class="toggle toggle-primary" checked />
                  </label>
                </div>
                
                <div class="form-control">
                  <label class="label cursor-pointer">
                    <span class="label-text">Alumni Update Alerts</span>
                    <input type="checkbox" class="toggle toggle-primary" />
                  </label>
                </div>
                
                <div class="form-control">
                  <label class="label cursor-pointer">
                    <span class="label-text">System Maintenance Alerts</span>
                    <input type="checkbox" class="toggle toggle-primary" checked />
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
    </div>
  </Layout>
</template>

<script setup>
import { ref } from 'vue'
import Layout from '@/components/layout/Layout.vue'
import { useRouter } from 'vue-router'
import { 
  User as IconUser,
  Settings as IconSettings,
  Archive as IconArchive
} from 'lucide-vue-next'

const router = useRouter()

// State
const activeTab = ref('profile')

const handleLogout = () => {
  router.push('/home')
}
</script>

<style scoped>
h1, p {
  color: var(--color-text-primary);
}
</style>