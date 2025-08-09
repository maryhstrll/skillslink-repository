<template>
  <Layout @logout="handleLogout">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold text-base-content">User Management</h1>
          <p class="text-base-content/70 mt-1">Manage user accounts and permissions</p>
        </div>
        <div class="flex gap-2">
          <button class="btn btn-outline" @click="exportUsers">
            <i class="fas fa-download"></i>
            Export
          </button>
          <button class="btn btn-primary" @click="showAddUserModal = true">
            <i class="fas fa-plus"></i>
            Add User
          </button>
        </div>
      </div>

      <!-- User Statistics -->
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
          <div class="stat-title">Pending</div>
          <div class="stat-value text-warning">{{ userStats.pending }}</div>
        </div>
        
        <div class="stat bg-base-100 rounded-lg shadow">
          <div class="stat-figure text-error">
            <i class="fas fa-user-times text-2xl"></i>
          </div>
          <div class="stat-title">Inactive</div>
          <div class="stat-value text-error">{{ userStats.inactive }}</div>
        </div>
      </div>

      <!-- User Approval Management (Admin Only) -->
      <UserApprovalManager />

      <!-- Search and Filter -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="form-control flex-1">
              <div class="input-group">
                <input 
                  v-model="searchQuery" 
                  type="text" 
                  placeholder="Search users..." 
                  class="input input-bordered flex-1"
                />
                <button class="btn btn-square">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
            <select v-model="selectedRole" class="select select-bordered">
              <option value="">All Roles</option>
              <option value="admin">Admin</option>
              <option value="user">User</option>
              <option value="alumni">Alumni</option>
            </select>
            <select v-model="selectedStatus" class="select select-bordered">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="pending">Pending</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Last Login</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in filteredUsers" :key="user.id">
                  <td>
                    <div class="flex items-center gap-3">
                      <div class="avatar placeholder">
                        <div class="bg-neutral text-neutral-content rounded-full w-10">
                          <span class="text-sm">{{ getUserInitials(user.name) }}</span>
                        </div>
                      </div>
                      <div>
                        <div class="font-bold">{{ user.name }}</div>
                        <div class="text-sm opacity-50">{{ user.username }}</div>
                      </div>
                    </div>
                  </td>
                  <td>{{ user.email }}</td>
                  <td>
                    <div class="badge" :class="getRoleBadgeClass(user.role)">
                      {{ user.role }}
                    </div>
                  </td>
                  <td>
                    <div class="badge" :class="getStatusBadgeClass(user.status)">
                      {{ user.status }}
                    </div>
                  </td>
                  <td>{{ formatDate(user.lastLogin) }}</td>
                  <td>
                    <div class="dropdown dropdown-end">
                      <div tabindex="0" role="button" class="btn btn-ghost btn-xs">
                        <i class="fas fa-ellipsis-v"></i>
                      </div>
                      <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                        <li><a @click="editUser(user)"><i class="fas fa-edit"></i> Edit</a></li>
                        <li><a @click="resetPassword(user)"><i class="fas fa-key"></i> Reset Password</a></li>
                        <li><a @click="toggleUserStatus(user)" :class="{ 'text-error': user.status === 'active' }">
                          <i :class="user.status === 'active' ? 'fas fa-ban' : 'fas fa-check'"></i>
                          {{ user.status === 'active' ? 'Deactivate' : 'Activate' }}
                        </a></li>
                      </ul>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div class="flex justify-center mt-4">
            <div class="join">
              <button class="join-item btn" :disabled="currentPage === 1" @click="currentPage--">«</button>
              <button class="join-item btn">Page {{ currentPage }}</button>
              <button class="join-item btn" :disabled="currentPage === totalPages" @click="currentPage++">»</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add User Modal -->
    <div v-if="showAddUserModal" class="modal modal-open">
      <div class="modal-box">
        <h3 class="font-bold text-lg">Add New User</h3>
        <form @submit.prevent="addUser" class="py-4">
          <div class="form-control">
            <label class="label">Name</label>
            <input v-model="newUser.name" type="text" class="input input-bordered" required>
          </div>
          <div class="form-control">
            <label class="label">Username</label>
            <input v-model="newUser.username" type="text" class="input input-bordered" required>
          </div>
          <div class="form-control">
            <label class="label">Email</label>
            <input v-model="newUser.email" type="email" class="input input-bordered" required>
          </div>
          <div class="form-control">
            <label class="label">Role</label>
            <select v-model="newUser.role" class="select select-bordered" required>
              <option value="user">User</option>
              <option value="admin">Admin</option>
              <option value="alumni">Alumni</option>
            </select>
          </div>
          <div class="modal-action">
            <button type="button" class="btn" @click="showAddUserModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary">Add User</button>
          </div>
        </form>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Layout from '@/components/Layout.vue'
import UserApprovalManager from '@/components/UserApprovalManager.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Data
const users = ref([
  {
    id: 1,
    name: 'John Doe',
    username: 'johndoe',
    email: 'john@example.com',
    role: 'admin',
    status: 'active',
    lastLogin: new Date('2024-01-15T10:30:00')
  },
  {
    id: 2,
    name: 'Sarah Johnson',
    username: 'sarahj',
    email: 'sarah@example.com',
    role: 'user',
    status: 'active',
    lastLogin: new Date('2024-01-14T15:45:00')
  },
  {
    id: 3,
    name: 'Mike Chen',
    username: 'mikechen',
    email: 'mike@example.com',
    role: 'alumni',
    status: 'pending',
    lastLogin: new Date('2024-01-10T09:20:00')
  }
])

const userStats = computed(() => ({
  total: users.value.length,
  active: users.value.filter(u => u.status === 'active').length,
  pending: users.value.filter(u => u.status === 'pending').length,
  inactive: users.value.filter(u => u.status === 'inactive').length
}))

// Search and filter
const searchQuery = ref('')
const selectedRole = ref('')
const selectedStatus = ref('')

const filteredUsers = computed(() => {
  return users.value.filter(user => {
    const matchesSearch = user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         user.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         user.username.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesRole = !selectedRole.value || user.role === selectedRole.value
    const matchesStatus = !selectedStatus.value || user.status === selectedStatus.value
    
    return matchesSearch && matchesRole && matchesStatus
  })
})

// Pagination
const currentPage = ref(1)
const itemsPerPage = 10
const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage))

// Modal
const showAddUserModal = ref(false)
const newUser = ref({
  name: '',
  username: '',
  email: '',
  role: 'user'
})

// Methods
const getUserInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'badge-error',
    user: 'badge-info',
    alumni: 'badge-success'
  }
  return classes[role] || 'badge-ghost'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'badge-success',
    inactive: 'badge-error',
    pending: 'badge-warning'
  }
  return classes[status] || 'badge-ghost'
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

const editUser = (user) => {
  console.log('Edit user:', user)
  // Implement edit functionality
}

const resetPassword = (user) => {
  console.log('Reset password for:', user)
  // Implement password reset
}

const toggleUserStatus = (user) => {
  user.status = user.status === 'active' ? 'inactive' : 'active'
  console.log('Toggle status for:', user)
}

const addUser = () => {
  const user = {
    id: users.value.length + 1,
    ...newUser.value,
    status: 'pending',
    lastLogin: new Date()
  }
  users.value.push(user)
  
  // Reset form
  newUser.value = { name: '', username: '', email: '', role: 'user' }
  showAddUserModal.value = false
}

const exportUsers = () => {
  console.log('Export users')
  // Implement export functionality
}

const handleLogout = () => {
  router.push('/home')
}

onMounted(() => {
  // Load users from API
  console.log('Users component mounted')
})
</script>