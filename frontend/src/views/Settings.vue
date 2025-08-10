<template>
  <Layout @logout="handleLogout">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-base-content">Settings</h1>
          <p class="text-base-content/70 mt-1">Configure application settings and preferences</p>
        </div>
        <button class="btn btn-primary" @click="saveAllSettings">
          <IconSave class="w-4 h-4" />
          Save All Changes
        </button>
      </div>

      <!-- Settings Tabs -->
      <div class="tabs tabs-boxed">
        <a 
          v-for="tab in tabs" 
          :key="tab.id"
          class="tab" 
          :class="{ 'tab-active': activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          <component :is="tab.icon" class="w-4 h-4 mr-2" />
          {{ tab.name }}
        </a>
      </div>

      <!-- General Settings -->
      <div v-if="activeTab === 'general'" class="space-y-6">
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">
              <IconSettings class="w-5 h-5" />
              General Settings
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Application Name</span>
                </label>
                <input 
                  v-model="settings.general.appName" 
                  type="text" 
                  class="input input-bordered" 
                />
              </div>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Time Zone</span>
                </label>
                <select v-model="settings.general.timezone" class="select select-bordered">
                  <option value="UTC">UTC</option>
                  <option value="America/New_York">Eastern Time</option>
                  <option value="America/Chicago">Central Time</option>
                  <option value="America/Los_Angeles">Pacific Time</option>
                </select>
              </div>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Date Format</span>
                </label>
                <select v-model="settings.general.dateFormat" class="select select-bordered">
                  <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                  <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                  <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                </select>
              </div>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Items Per Page</span>
                </label>
                <input 
                  v-model.number="settings.general.itemsPerPage" 
                  type="number" 
                  min="5" 
                  max="100" 
                  class="input input-bordered" 
                />
              </div>
            </div>

            <div class="form-control">
              <label class="label cursor-pointer">
                <span class="label-text">Enable Dark Mode</span>
                <input 
                  v-model="settings.general.darkMode" 
                  type="checkbox" 
                  class="toggle toggle-primary" 
                />
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Security Settings -->
      <div v-if="activeTab === 'security'" class="space-y-6">
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">
              <IconShield class="w-5 h-5" />
              Security Settings
            </h2>
            
            <div class="space-y-4">
              <div class="form-control">
                <label class="label cursor-pointer">
                  <span class="label-text">Require Two-Factor Authentication</span>
                  <input 
                    v-model="settings.security.twoFactor" 
                    type="checkbox" 
                    class="toggle toggle-primary" 
                  />
                </label>
              </div>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Session Timeout (minutes)</span>
                </label>
                <input 
                  v-model.number="settings.security.sessionTimeout" 
                  type="number" 
                  min="5" 
                  max="480" 
                  class="input input-bordered" 
                />
              </div>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Password Policy</span>
                </label>
                <select v-model="settings.security.passwordPolicy" class="select select-bordered">
                  <option value="basic">Basic (8+ characters)</option>
                  <option value="medium">Medium (8+ chars, numbers, symbols)</option>
                  <option value="strong">Strong (12+ chars, mixed case, numbers, symbols)</option>
                </select>
              </div>
              
              <div class="form-control">
                <label class="label cursor-pointer">
                  <span class="label-text">Enable Login Notifications</span>
                  <input 
                    v-model="settings.security.loginNotifications" 
                    type="checkbox" 
                    class="toggle toggle-primary" 
                  />
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Email Settings -->
      <div v-if="activeTab === 'email'" class="space-y-6">
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">
              <IconMail class="w-5 h-5" />
              Email Configuration
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="form-control">
                <label class="label">
                  <span class="label-text">SMTP Server</span>
                </label>
                <input 
                  v-model="settings.email.smtpServer" 
                  type="text" 
                  class="input input-bordered" 
                  placeholder="smtp.gmail.com"
                />
              </div>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text">SMTP Port</span>
                </label>
                <input 
                  v-model.number="settings.email.smtpPort" 
                  type="number" 
                  class="input input-bordered" 
                  placeholder="587"
                />
              </div>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text">From Email</span>
                </label>
                <input 
                  v-model="settings.email.fromEmail" 
                  type="email" 
                  class="input input-bordered" 
                  placeholder="noreply@skillslink.com"
                />
              </div>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text">From Name</span>
                </label>
                <input 
                  v-model="settings.email.fromName" 
                  type="text" 
                  class="input input-bordered" 
                  placeholder="SkillsLink"
                />
              </div>
            </div>

            <div class="form-control">
              <label class="label cursor-pointer">
                <span class="label-text">Enable Email Notifications</span>
                <input 
                  v-model="settings.email.enabled" 
                  type="checkbox" 
                  class="toggle toggle-primary" 
                />
              </label>
            </div>

            <div class="flex gap-2 mt-4">
              <button class="btn btn-outline" @click="testEmailConnection">
                <IconSend class="w-4 h-4" />
                Test Connection
              </button>
              <button class="btn btn-outline" @click="sendTestEmail">
                <IconMail class="w-4 h-4" />
                Send Test Email
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Backup Settings -->
      <div v-if="activeTab === 'backup'" class="space-y-6">
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">
              <IconDatabase class="w-5 h-5" />
              Backup & Recovery
            </h2>
            
            <div class="space-y-4">
              <div class="form-control">
                <label class="label cursor-pointer">
                  <span class="label-text">Enable Automatic Backups</span>
                  <input 
                    v-model="settings.backup.autoBackup" 
                    type="checkbox" 
                    class="toggle toggle-primary" 
                  />
                </label>
              </div>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Backup Frequency</span>
                </label>
                <select v-model="settings.backup.frequency" class="select select-bordered">
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                </select>
              </div>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Retention Period (days)</span>
                </label>
                <input 
                  v-model.number="settings.backup.retentionDays" 
                  type="number" 
                  min="1" 
                  max="365" 
                  class="input input-bordered" 
                />
              </div>
            </div>

            <div class="divider"></div>

            <div class="flex gap-2">
              <button class="btn btn-primary" @click="createBackup">
                <IconDownload class="w-4 h-4" />
                Create Backup Now
              </button>
              <button class="btn btn-outline">
                <IconUpload class="w-4 h-4" />
                Restore from Backup
              </button>
            </div>

            <!-- Recent Backups -->
            <div class="mt-6">
              <h3 class="text-lg font-semibold mb-3">Recent Backups</h3>
              <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Size</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="backup in recentBackups" :key="backup.id">
                      <td>{{ formatDate(backup.date) }}</td>
                      <td>{{ backup.size }}</td>
                      <td>
                        <div class="badge badge-success">{{ backup.status }}</div>
                      </td>
                      <td>
                        <button class="btn btn-xs btn-outline">Download</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
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
import Layout from '@/components/Layout.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Active tab
const activeTab = ref('general')

// Tabs configuration
const tabs = [
  { id: 'general', name: 'General', icon: 'IconSettings' },
  { id: 'security', name: 'Security', icon: 'IconShield' },
  { id: 'email', name: 'Email', icon: 'IconMail' },
  { id: 'backup', name: 'Backup', icon: 'IconDatabase' }
]

// Settings data
const settings = ref({
  general: {
    appName: 'SkillsLink',
    timezone: 'UTC',
    dateFormat: 'MM/DD/YYYY',
    itemsPerPage: 20,
    darkMode: false
  },
  security: {
    twoFactor: false,
    sessionTimeout: 30,
    passwordPolicy: 'medium',
    loginNotifications: true
  },
  email: {
    smtpServer: '',
    smtpPort: 587,
    fromEmail: '',
    fromName: 'SkillsLink',
    enabled: false
  },
  backup: {
    autoBackup: true,
    frequency: 'weekly',
    retentionDays: 30
  }
})

// Recent backups data
const recentBackups = ref([
  { id: 1, date: new Date('2024-01-15'), size: '2.4 MB', status: 'Complete' },
  { id: 2, date: new Date('2024-01-08'), size: '2.3 MB', status: 'Complete' },
  { id: 3, date: new Date('2024-01-01'), size: '2.2 MB', status: 'Complete' }
])

// Methods
const saveAllSettings = () => {
  console.log('Saving settings:', settings.value)
  // Implement save functionality
  alert('Settings saved successfully!')
}

const testEmailConnection = () => {
  console.log('Testing email connection...')
  alert('Email connection test initiated')
}

const sendTestEmail = () => {
  console.log('Sending test email...')
  alert('Test email sent!')
}

const createBackup = () => {
  console.log('Creating backup...')
  alert('Backup creation initiated')
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const handleLogout = () => {
  router.push('/home')
}
</script>