<template>
  <Layout @logout="handleLogout">
    <!-- Authentication Check -->
    <div v-if="!isAuthenticated" class="flex justify-center items-center min-h-screen">
      <div class="text-center">
        <span class="loading loading-spinner loading-lg"></span>
        <p class="mt-4">Checking authentication...</p>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="space-y-6">
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
      <UserStatistics :user-stats="userStats" />

      <RecentActivity/>

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
                  placeholder="Search users by name, email, or student ID..." 
                  class="input input-bordered flex-1"
                />
                <button class="btn btn-square" @click="fetchUsers(1)">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
            <select v-model="selectedRole" class="select select-bordered" @change="fetchUsers(1)">
              <option value="">All Roles</option>
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
              <option value="alumni">Alumni</option>
            </select>
            <select v-model="selectedStatus" class="select select-bordered" @change="fetchUsers(1)">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="pending">Pending Approval</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center items-center py-8">
            <span class="loading loading-spinner loading-lg"></span>
            <span class="ml-2">Loading users...</span>
          </div>
          
          <!-- Error State -->
          <div v-else-if="error" class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <span>{{ error }}</span>
            <button @click="fetchUsers()" class="btn btn-sm btn-outline">Retry</button>
          </div>
          
          <!-- Empty State -->
          <div v-else-if="users.length === 0" class="text-center py-8">
            <i class="fas fa-users text-6xl text-base-300 mb-4"></i>
            <p class="text-xl font-semibold text-base-content/70">No users found</p>
            <p class="text-base-content/50">Try adjusting your search criteria</p>
          </div>
          
          <!-- Users Table -->
          <div v-else class="overflow-x-auto">
            <table class="table table-zebra w-full">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Program</th>
                  <th>Last Login</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id">
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
                    <!-- Show approval status with priority: rejected > pending > regular status -->
                    <div v-if="user.approval_status === 'rejected'" class="badge badge-error badge-sm">
                      Rejected
                    </div>
                    <div v-else-if="user.approval_status === 'pending'" class="badge badge-warning badge-sm">
                      Pending
                    </div>
                    <div v-else class="badge badge-sm" :class="getStatusBadgeClass(user.status)">
                      {{ user.status }}
                    </div>
                  </td>
                  <td>{{ user.program || 'N/A' }}</td>
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
          <div v-if="users.length > 0" class="flex justify-between items-center mt-4">
            <div class="text-sm text-base-content/70">
              Showing {{ users.length }} of {{ totalRecords }} users
            </div>
            <div class="join">
              <button 
                class="join-item btn btn-sm" 
                :disabled="currentPage === 1" 
                @click="changePage(currentPage - 1)"
              >
                «
              </button>
              <button class="join-item btn btn-sm">
                Page {{ currentPage }} of {{ totalPages }}
              </button>
              <button 
                class="join-item btn btn-sm" 
                :disabled="currentPage === totalPages" 
                @click="changePage(currentPage + 1)"
              >
                »
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add User Modal -->
    <div v-if="showAddUserModal" class="modal modal-open">
      <div class="modal-box max-w-lg">
        <h3 class="font-bold text-lg">Add New User</h3>
        <form @submit.prevent="addUser" class="py-4 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-control">
              <label class="label">
                <span class="label-text">First Name *</span>
              </label>
              <input v-model="newUser.first_name" type="text" class="input input-bordered" required>
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text">Last Name *</span>
              </label>
              <input v-model="newUser.last_name" type="text" class="input input-bordered" required>
            </div>
          </div>
          
          <div class="form-control">
            <label class="label">
              <span class="label-text">Middle Name</span>
            </label>
            <input v-model="newUser.middle_name" type="text" class="input input-bordered">
          </div>
          
          <div class="form-control">
            <label class="label">
              <span class="label-text">Email *</span>
            </label>
            <input v-model="newUser.email" type="email" class="input input-bordered" required>
          </div>
          
          <div class="form-control">
            <label class="label">
              <span class="label-text">Password *</span>
            </label>
            <input v-model="newUser.password" type="password" class="input input-bordered" required>
          </div>
          
          <div class="form-control">
            <label class="label">
              <span class="label-text">Student ID</span>
            </label>
            <input v-model="newUser.student_id" type="text" class="input input-bordered">
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-control">
              <label class="label">
                <span class="label-text">Role *</span>
              </label>
              <select v-model="newUser.role" class="select select-bordered" required>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="alumni">Alumni</option>
              </select>
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text">Program *</span>
              </label>
              <select v-model.number="newUser.program_id" class="select select-bordered" required>
                <option v-for="program in programs" :key="program.id" :value="program.id">
                  {{ program.name }}
                </option>
              </select>
            </div>
          </div>
          
          <div class="modal-action">
            <button type="button" class="btn" @click="showAddUserModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <span v-if="loading" class="loading loading-spinner loading-sm"></span>
              Add User
            </button>
          </div>
        </form>
      </div>
    </div>
    <!-- End Main Content -->
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import Layout from '@/components/Layout.vue'
import UserApprovalManager from '@/components/UserApprovalManager.vue'
import UserStatistics from '@/components/UserStatistics.vue'
import RecentActivity from '@/components/RecentActivity.vue'
import { useRouter } from 'vue-router'
import usersService from '@/services/users-test.js'
import authService from '@/services/auth.js'

const router = useRouter()

// Data
const users = ref([]) // Filtered users for display
const allUsers = ref([]) // All users for statistics
const programs = ref([])
const loading = ref(false)
const error = ref('')
const isAuthenticated = ref(false)
const userRole = ref('')

const userStats = computed(() => ({
  total: allUsers.value.length,
  active: allUsers.value.filter(u => u.status === 'active' && u.approval_status === 'approved').length,
  pending: allUsers.value.filter(u => u.approval_status === 'pending').length,
  rejected: allUsers.value.filter(u => u.approval_status === 'rejected').length,
  inactive: allUsers.value.filter(u => u.status === 'inactive').length
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
    
    // Handle status filtering for both regular status and approval status
    let matchesStatus = true
    if (selectedStatus.value) {
      if (selectedStatus.value === 'pending') {
        matchesStatus = user.approval_status === 'pending'
      } else if (selectedStatus.value === 'rejected') {
        matchesStatus = user.approval_status === 'rejected'
      } else {
        // For active/inactive, check regular status AND ensure not pending/rejected
        matchesStatus = user.status === selectedStatus.value && 
                       user.approval_status !== 'pending' && 
                       user.approval_status !== 'rejected'
      }
    }
    
    return matchesSearch && matchesRole && matchesStatus
  })
})

// Pagination
const currentPage = ref(1)
const itemsPerPage = 20
const totalPages = ref(1)
const totalRecords = ref(0)

// Modal
const showAddUserModal = ref(false)
const newUser = ref({
  first_name: '',
  last_name: '',
  middle_name: '',
  email: '',
  password: '',
  role: 'alumni',
  program_id: 1,
  student_id: ''
})

// Update program_id when programs are loaded
watch(programs, (newPrograms) => {
  if (newPrograms.length > 0 && !newUser.value.program_id) {
    newUser.value.program_id = newPrograms[0].id
  }
}, { immediate: true })

// Methods
const checkAuthentication = async () => {
  // For testing, skip authentication check
  isAuthenticated.value = true
  userRole.value = 'admin'
  return true
  
  /*
  try {
    const authResult = await authService.checkAuth()
    isAuthenticated.value = authResult.loggedIn
    
    if (authResult.loggedIn) {
      userRole.value = authResult.user?.role || ''
      
      // Check if user has admin/staff privileges
      if (!['admin', 'staff'].includes(userRole.value)) {
        error.value = 'You do not have permission to view this page'
        return false
      }
      return true
    } else {
      router.push('/login')
      return false
    }
  } catch (err) {
    console.error('Authentication check failed:', err)
    error.value = 'Authentication failed'
    return false
  }
  */
}

const fetchAllUsers = async () => {
  try {
    const result = await usersService.getUsers({ limit: '1000' }) // Get a large number to get all users
    if (result.success && result.data.success) {
      allUsers.value = result.data.data || []
    }
  } catch (err) {
    console.error('Error fetching all users for statistics:', err)
  }
}

const fetchUsers = async (page = 1) => {
  if (!isAuthenticated.value) {
    const authOk = await checkAuthentication()
    if (!authOk) return
  }

  loading.value = true
  error.value = ''
  
  try {
    const params = {
      page: page.toString(),
      limit: itemsPerPage.toString()
    }
    
    if (selectedRole.value) params.role = selectedRole.value
    if (selectedStatus.value) params.status = selectedStatus.value
    if (searchQuery.value) params.search = searchQuery.value
    
    const result = await usersService.getUsers(params)
    
    if (result.success && result.data.success) {
      users.value = result.data.data || []
      currentPage.value = result.data.pagination?.current_page || 1
      totalPages.value = result.data.pagination?.total_pages || 1
      totalRecords.value = result.data.pagination?.total_records || 0
    } else {
      error.value = result.error || result.data?.error || 'Failed to fetch users'
      
      // Handle authentication errors
      if (result.status === 401) {
        router.push('/login')
      } else if (result.status === 403) {
        error.value = 'You do not have permission to view this page'
      }
    }
  } catch (err) {
    console.error('Error fetching users:', err)
    error.value = 'Failed to connect to server'
  } finally {
    loading.value = false
  }
}

const fetchPrograms = async () => {
  try {
    const result = await usersService.getPrograms()
    if (result.success && result.data.success) {
      programs.value = result.data.data || []
    }
  } catch (err) {
    console.error('Error fetching programs:', err)
  }
}

const getUserInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'badge-error',
    staff: 'badge-warning',
    alumni: 'badge-success'
  }
  return classes[role] || 'badge-ghost'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'badge-success',
    inactive: 'badge-error',
    pending: 'badge-warning',
    rejected: 'badge-error'
  }
  return classes[status] || 'badge-ghost'
}

const formatDate = (date) => {
  if (!date) return 'Never'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const editUser = async (user) => {
  console.log('Edit user:', user)
  // TODO: Implement edit functionality with modal
}

const resetPassword = async (user) => {
  if (confirm(`Reset password for ${user.name}?`)) {
    // TODO: Implement password reset API call
    console.log('Reset password for:', user)
  }
}

const toggleUserStatus = async (user) => {
  const newStatus = user.status === 'active' ? 'inactive' : 'active'
  const isActive = newStatus === 'active' ? 1 : 0
  
  try {
    const result = await usersService.toggleUserStatus(user.id, isActive)
    
    if (result.success) {
      user.status = newStatus
    } else {
      alert('Failed to update user status: ' + result.error)
    }
  } catch (err) {
    console.error('Error updating user status:', err)
    alert('Failed to update user status')
  }
}

const addUser = async () => {
  try {
    const result = await usersService.createUser(newUser.value)
    
    if (result.success) {
      // Reset form
      newUser.value = { 
        first_name: '', 
        last_name: '', 
        middle_name: '',
        email: '', 
        password: '',
        role: 'alumni',
        program_id: programs.value[0]?.id || 1,
        student_id: ''
      }
      showAddUserModal.value = false
      
      // Refresh users list
      await fetchUsers(currentPage.value)
    } else {
      alert('Failed to create user: ' + result.error)
    }
  } catch (err) {
    console.error('Error creating user:', err)
    alert('Failed to create user')
  }
}

const exportUsers = () => {
  // Create CSV content
  const csvContent = [
    ['Name', 'Email', 'Role', 'Status', 'Program', 'Created At'].join(','),
    ...users.value.map(user => [
      `"${user.name}"`,
      `"${user.email}"`,
      `"${user.role}"`,
      `"${user.status}"`,
      `"${user.program || 'N/A'}"`,
      `"${formatDate(user.createdAt)}"`
    ].join(','))
  ].join('\n')
  
  // Download CSV file
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  link.setAttribute('href', url)
  link.setAttribute('download', `users_export_${new Date().toISOString().split('T')[0]}.csv`)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    fetchUsers(page)
  }
}

const handleLogout = () => {
  router.push('/home')
}

// Watchers for real-time filtering
let searchTimeout = null

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchUsers(1)
  }, 500)
}

watch(searchQuery, debouncedSearch)
watch([selectedRole, selectedStatus], () => {
  fetchUsers(1)
})

onMounted(async () => {
  const authOk = await checkAuthentication()
  if (authOk) {
    await Promise.all([
      fetchAllUsers(), // Fetch all users for statistics
      fetchUsers(),    // Fetch filtered users for display
      fetchPrograms()
    ])
  }
})
</script>