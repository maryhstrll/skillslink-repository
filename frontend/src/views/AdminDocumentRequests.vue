<template>
  <Layout @logout="handleLogout">
    <div class="min-h-screen bg-gradient-to-br from-[#081F37] to-[#1E549F] p-4 md:p-6 lg:p-8">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 shadow-xl border border-white/20">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="text-white">
              <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-2 bg-gradient-to-r from-[#5FC9F3] to-[#2E79BA] bg-clip-text text-transparent">
                Document Request Management
              </h1>
              <p class="text-white/80 text-sm md:text-base">Manage and process alumni document requests</p>
            </div>
            <div class="flex gap-2">
              <div class="stats shadow bg-white/10 border border-white/20">
                <div class="stat">
                  <div class="stat-title text-white/70">Total Requests</div>
                  <div class="stat-value text-[#5FC9F3] text-2xl">{{ requests.length }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 shadow-xl border border-white/20">
          <div class="flex flex-wrap gap-4 items-center">
            <div class="form-control">
              <label class="label">
                <span class="label-text text-white">Filter by Status</span>
              </label>
              <select v-model="statusFilter" class="select select-bordered select-sm bg-white text-gray-800">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Processing">Processing</option>
                <option value="Ready for Pickup">Ready for Pickup</option>
                <option value="Completed">Completed</option>
              </select>
            </div>
            
            <div class="form-control">
              <label class="label">
                <span class="label-text text-white">Filter by Document Type</span>
              </label>
              <select v-model="documentTypeFilter" class="select select-bordered select-sm bg-white text-gray-800">
                <option value="">All Types</option>
                <option value="Transcript of Records">Transcript of Records</option>
                <option value="Transcript of Competency">Transcript of Competency</option>
                <option value="Diploma">Diploma</option>
                <option value="Certificate of Training">Certificate of Training</option>
              </select>
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text text-white">Search Alumni</span>
              </label>
              <input 
                type="text" 
                placeholder="Search by name or student ID..." 
                class="input input-bordered input-sm bg-white text-gray-800"
                v-model="searchQuery"
              />
            </div>

            <button class="btn btn-outline btn-sm text-white border-white hover:bg-white hover:text-gray-800 mt-6" @click="clearFilters">
              Clear Filters
            </button>
          </div>
        </div>

        <!-- Requests Table -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg shadow-xl border border-white/20 overflow-hidden">
          <div v-if="loading" class="flex justify-center items-center py-12">
            <span class="loading loading-spinner loading-lg text-white"></span>
          </div>
          
          <div v-else-if="filteredRequests.length === 0" class="text-center py-12 text-white">
            <i class="fas fa-file-alt text-6xl mb-4 text-white/30"></i>
            <p class="text-xl mb-2">No document requests found</p>
            <p class="text-white/70">{{ requests.length === 0 ? 'No requests have been submitted yet' : 'Try adjusting your filters' }}</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="table w-full">
              <thead class="bg-[#2E79BA]/20 text-white">
                <tr>
                  <th class="text-white">Alumni</th>
                  <th class="text-white">Student ID</th>
                  <th class="text-white">Program</th>
                  <th class="text-white">Document Type</th>
                  <th class="text-white">Purpose</th>
                  <th class="text-white">Status</th>
                  <th class="text-white">Request Date</th>
                  <th class="text-white">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="request in filteredRequests" :key="request.request_id" class="hover:bg-white/5 text-white">
                  <td class="font-medium">{{ request.full_name }}</td>
                  <td class="text-white/80">{{ request.student_id }}</td>
                  <td class="text-white/80">{{ request.program_name }}</td>
                  <td>
                    <div class="flex items-center gap-3">
                      <i :class="getDocumentTypeIcon(request.document_type)" class="text-[#5FC9F3]"></i>
                      <span>{{ request.document_type }}</span>
                    </div>
                  </td>
                  <td class="text-white/80 max-w-xs truncate">
                    {{ request.purpose || 'Not specified' }}
                  </td>
                  <td>
                    <div class="flex items-center gap-2">
                      <i :class="getStatusIcon(request.status)" class="text-sm"></i>
                      <span class="badge" :class="getStatusClass(request.status)">
                        {{ request.status }}
                      </span>
                    </div>
                  </td>
                  <td class="text-white/80">
                    {{ formatDate(request.request_date) }}
                  </td>
                  <td>
                    <div class="flex gap-2">
                      <button 
                        class="btn btn-sm bg-[#5FC9F3] text-white border-none hover:bg-[#2E79BA]"
                        @click="openStatusModal(request)"
                        :disabled="updating">
                        <i class="fas fa-edit w-3 h-3"></i>
                        Update
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Update Status Modal -->
    <div v-if="showStatusModal" class="modal modal-open">
      <div class="modal-box bg-white relative">
        <button class="btn btn-sm btn-circle absolute right-2 top-2" @click="closeStatusModal">✕</button>
        <h3 class="font-bold text-lg text-gray-800 mb-4">Update Request Status</h3>
        
        <div v-if="selectedRequest" class="space-y-4">
          <!-- Request Details -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-800 mb-2">Request Details</h4>
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div><strong>Alumni:</strong> {{ selectedRequest.full_name }}</div>
              <div><strong>Student ID:</strong> {{ selectedRequest.student_id }}</div>
              <div><strong>Document:</strong> {{ selectedRequest.document_type }}</div>
              <div><strong>Current Status:</strong> 
                <span class="badge ml-1" :class="getStatusClass(selectedRequest.status)">
                  {{ selectedRequest.status }}
                </span>
              </div>
            </div>
            <div v-if="selectedRequest.purpose" class="mt-2">
              <strong>Purpose:</strong> {{ selectedRequest.purpose }}
            </div>
          </div>

          <!-- Status Update Form -->
          <form @submit.prevent="updateStatus" class="space-y-4">
            <div class="form-control">
              <label class="label">
                <span class="label-text text-gray-700">New Status *</span>
              </label>
              <select v-model="newStatus" class="select select-bordered w-full bg-white text-gray-800" required>
                <option value="">Select new status</option>
                <option value="Pending">Pending</option>
                <option value="Processing">Processing</option>
                <option value="Ready for Pickup">Ready for Pickup</option>
                <option value="Completed">Completed</option>
              </select>
            </div>

            <div class="modal-action">
              <button type="button" class="btn btn-outline" @click="closeStatusModal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="updating || !newStatus">
                <span v-if="updating" class="loading loading-spinner loading-sm"></span>
                {{ updating ? 'Updating...' : 'Update Status' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import Layout from '@/components/Layout.vue'
import { useRouter } from 'vue-router'
import documentRequestService from '@/services/documentRequest.js'
import { messageService } from '@/services/messageService.js'

const router = useRouter()

// State
const requests = ref([])
const loading = ref(false)
const updating = ref(false)
const showStatusModal = ref(false)
const selectedRequest = ref(null)
const newStatus = ref('')

// Filters
const statusFilter = ref('')
const documentTypeFilter = ref('')
const searchQuery = ref('')

// Computed
const filteredRequests = computed(() => {
  let filtered = requests.value

  // Filter by status
  if (statusFilter.value) {
    filtered = filtered.filter(request => request.status === statusFilter.value)
  }

  // Filter by document type
  if (documentTypeFilter.value) {
    filtered = filtered.filter(request => request.document_type === documentTypeFilter.value)
  }

  // Filter by search query (name or student ID)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(request => 
      request.full_name.toLowerCase().includes(query) ||
      request.student_id.toLowerCase().includes(query)
    )
  }

  return filtered
})

// Methods
const fetchRequests = async () => {
  try {
    loading.value = true
    const data = await documentRequestService.getAll()
    requests.value = data
  } catch (error) {
    console.error('Error fetching document requests:', error)
    messageService.toast('Failed to load document requests', 'error')
  } finally {
    loading.value = false
  }
}

const openStatusModal = (request) => {
  selectedRequest.value = request
  newStatus.value = ''
  showStatusModal.value = true
}

const closeStatusModal = () => {
  showStatusModal.value = false
  selectedRequest.value = null
  newStatus.value = ''
}

const updateStatus = async () => {
  if (!selectedRequest.value || !newStatus.value) return

  try {
    updating.value = true
    
    const result = await documentRequestService.updateStatus(selectedRequest.value.request_id, newStatus.value)
    
    if (result.success) {
      messageService.toast('Request status updated successfully!', 'success')
      closeStatusModal()
      await fetchRequests() // Refresh the list
    } else {
      messageService.alert(result.message || 'Failed to update status', 'error')
    }
  } catch (error) {
    console.error('Error updating status:', error)
    messageService.alert('Failed to update status. Please try again.', 'error')
  } finally {
    updating.value = false
  }
}

const clearFilters = () => {
  statusFilter.value = ''
  documentTypeFilter.value = ''
  searchQuery.value = ''
}

const getStatusClass = (status) => {
  return documentRequestService.getStatusClass(status)
}

const getStatusIcon = (status) => {
  return documentRequestService.getStatusIcon(status)
}

const getDocumentTypeIcon = (documentType) => {
  return documentRequestService.getDocumentTypeIcon(documentType)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
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

// Lifecycle
onMounted(() => {
  fetchRequests()
})
</script>

<style scoped>
/* Custom scrollbar */
.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #5FC9F3;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #2E79BA;
}

/* Animation */
.btn, .card, .modal-box {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Table styling */
.table th {
  background-color: rgba(46, 121, 186, 0.2) !important;
  color: white !important;
  font-weight: 600;
}

.table td {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.table tbody tr:hover {
  background-color: rgba(255, 255, 255, 0.05) !important;
}

.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
