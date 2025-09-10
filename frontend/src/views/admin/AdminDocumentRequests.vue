<template>
  <Layout @logout="handleLogout">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 class="text-3xl font-bold text-base-content">Document Request Management</h1>
            <p class="text-base-content/70 mt-1">
              Manage and process alumni document requests
            </p>
          </div>
          <div class="flex gap-2">
            <button 
              class="btn btn-primary-custom shadow-lg hover:shadow-xl transition-all duration-200"
              @click="fetchRequests"
              :disabled="loading"
            >
              <IconRefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
              Refresh
            </button>
          </div>
        </div>

        <!-- Filters Section -->
        <FilterSection
          title="Filter Document Requests"
          v-model="filters"
          :filters="filterConfig"
          :total-count="requests.length"
          :filtered-count="filteredRequests.length"
          item-label="requests"
          @clear="onFiltersClear"
        />

        <!-- Document Requests DataTable -->
        <DataTable
          title="Document Requests"
          :title-icon="IconFileText"
          :data="filteredRequests"
          :columns="tableColumns"
          :loading="loading"
          item-label="requests"
          empty-title="No document requests found"
          :empty-description="requests.length === 0 ? 'No requests have been submitted yet' : 'Try adjusting your filters to see more results'"
          :empty-icon="IconFileText"
          key-field="request_id"
          loading-text="Loading document requests..."
        >
          <!-- Custom cell for document type with icon -->
          <template #cell-document_type="{ value }">
            <div class="flex items-center gap-2">
              <IconFileText class="w-4 h-4" style="color: var(--color-primary);" />
              <span>{{ value }}</span>
            </div>
          </template>

          <!-- Custom cell for status with badge -->
          <template #cell-status="{ value }">
            <div class="flex items-center gap-2">
              <i :class="getStatusIcon(value)" class="text-sm"></i>
              <span class="badge" :class="getStatusClass(value)">
                {{ value }}
              </span>
            </div>
          </template>

          <!-- Custom cell for request date -->
          <template #cell-request_date="{ value }">
            <span class="text-sm opacity-80">
              {{ formatDate(value) }}
            </span>
          </template>

          <!-- Custom cell for actions -->
          <template #cell-actions="{ item }">
            <div class="flex gap-2">
              <button 
                class="btn btn-sm btn-primary-custom shadow-lg hover:shadow-xl transition-all duration-200"
                @click="viewRequestDetails(item)"
                title="View Details"
              >
                <IconEye class="w-4 h-4" />
              </button>
              <button 
                class="btn btn-sm btn-secondary-custom shadow-lg hover:shadow-xl transition-all duration-200"
                @click="openStatusModal(item)"
                title="Update Status"
                :disabled="updating"
              >
                <IconEdit class="w-4 h-4" />
              </button>
            </div>
          </template>
        </DataTable>
      </div>

    <!-- Update Status Modal -->
    <div v-if="showStatusModal" class="modal modal-open">
      <div class="modal-box bg-white relative max-w-2xl shadow-2xl">
        <!-- Close Button -->
        <button 
          class="btn btn-sm btn-circle absolute right-4 top-4 bg-gray-100 hover:bg-gray-200 border-none text-gray-600 hover:text-gray-800 transition-colors" 
          @click="closeStatusModal"
        >
          ✕
        </button>
        
        <!-- Header -->
        <div class="border-b border-gray-200 pb-4 mb-6">
          <h3 class="font-bold text-2xl text-gray-800 flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-r from-[#5FC9F3] to-[#2E79BA] rounded-lg flex items-center justify-center">
              <i class="fas fa-edit text-white text-sm"></i>
            </div>
            Update Request Status
          </h3>
          <p class="text-gray-500 mt-2">Modify the processing status of this document request</p>
        </div>
        
        <div v-if="selectedRequest" class="space-y-6">
          <!-- Request Details Card -->
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-100">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <i class="fas fa-info text-white text-sm"></i>
              </div>
              <h4 class="font-semibold text-gray-800 text-lg">Request Information</h4>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-3">
                <div class="flex flex-col">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Alumni Name</span>
                  <span class="text-gray-800 font-medium">{{ selectedRequest.full_name }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Student ID</span>
                  <span class="text-gray-800 font-mono">{{ selectedRequest.student_id }}</span>
                </div>
              </div>
              
              <div class="space-y-3">
                <div class="flex flex-col">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Document Type</span>
                  <div class="flex items-center gap-2">
                    <i :class="getDocumentTypeIcon(selectedRequest.document_type)" class="text-blue-500"></i>
                    <span class="text-gray-800 font-medium">{{ selectedRequest.document_type }}</span>
                  </div>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Current Status</span>
                  <div class="flex items-center gap-2 mt-1">
                    <i :class="getStatusIcon(selectedRequest.status)" class="text-sm"></i>
                    <span class="badge badge-lg" :class="getStatusClass(selectedRequest.status)">
                      {{ selectedRequest.status }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-if="selectedRequest.purpose" class="mt-4 pt-4 border-t border-blue-200">
              <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Purpose</span>
              <p class="text-gray-800 mt-1 bg-white p-3 rounded-lg border border-blue-100">
                {{ selectedRequest.purpose }}
              </p>
            </div>
          </div>

          <!-- Status Update Form -->
          <form @submit.prevent="updateStatus" class="space-y-6">
            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                  <i class="fas fa-sync-alt text-white text-sm"></i>
                </div>
                <h4 class="font-semibold text-gray-800 text-lg">Update Status</h4>
              </div>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text text-gray-700 font-medium">New Status *</span>
                </label>
                <select 
                  v-model="newStatus" 
                  class="select select-bordered w-full bg-white text-gray-800 border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors" 
                  required
                >
                  <option value="" disabled>Select new status</option>
                  <option value="Pending" class="flex items-center">
                    🕐 Pending
                  </option>
                  <option value="Processing" class="flex items-center">
                    ⚙️ Processing
                  </option>
                  <option value="Ready for Pickup" class="flex items-center">
                    📋 Ready for Pickup
                  </option>
                  <option value="Completed" class="flex items-center">
                    ✅ Completed
                  </option>
                </select>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
              <button 
                type="button" 
                class="btn btn-outline px-6 py-2 border-gray-300 text-gray-600 hover:bg-gray-50 hover:border-gray-400 transition-colors" 
                @click="closeStatusModal"
              >
                <i class="fas fa-times mr-2"></i>
                Cancel
              </button>
              <button 
                type="submit" 
                class="btn bg-gradient-to-r from-[#5FC9F3] to-[#2E79BA] text-white border-none hover:from-[#2E79BA] hover:to-[#1a5490] px-6 py-2 shadow-lg hover:shadow-xl transition-all duration-200" 
                :disabled="updating || !newStatus"
                :class="{ 'opacity-50 cursor-not-allowed': updating || !newStatus }"
              >
                <span v-if="updating" class="loading loading-spinner loading-sm mr-2"></span>
                <i v-else class="fas fa-save mr-2"></i>
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
import Layout from '@/components/layout/Layout.vue'
import DataTable from '@/components/tables/DataTable.vue'
import FilterSection from '@/components/ui/FilterSection.vue'
import { useRouter } from 'vue-router'
import documentRequestService from '@/services/documentRequest.js'
import { messageService } from '@/services/messageService.js'
import { 
  FileText as IconFileText,
  Users as IconUsers,
  Filter as IconFilter,
  Eye as IconEye,
  Edit as IconEdit,
  RefreshCw as IconRefreshCw
} from 'lucide-vue-next'

const router = useRouter()

// State
const requests = ref([])
const loading = ref(false)
const updating = ref(false)
const showStatusModal = ref(false)
const selectedRequest = ref(null)
const newStatus = ref('')

// Filter states
const filters = ref({
  search: '',
  status: '',
  documentType: ''
})

// Filter configuration
const filterConfig = computed(() => [
  {
    type: 'search',
    key: 'search',
    label: 'Search Alumni',
    placeholder: 'Search by name or student ID...',
    containerClass: 'flex-1'
  },
  {
    type: 'select',
    key: 'status',
    label: 'Filter by Status',
    defaultOption: 'All Statuses',
    options: ['Pending', 'Processing', 'Ready for Pickup', 'Completed'],
    containerClass: 'min-w-[180px]'
  },
  {
    type: 'select',
    key: 'documentType',
    label: 'Filter by Document Type',
    defaultOption: 'All Types',
    options: ['Transcript of Records', 'Transcript of Competency', 'Diploma', 'Certificate of Training'],
    containerClass: 'min-w-[200px]'
  }
])

// Computed
const filteredRequests = computed(() => {
  let filtered = requests.value

  // Search filter
  if (filters.value.search?.trim()) {
    const query = filters.value.search.toLowerCase().trim()
    filtered = filtered.filter(request => 
      request.full_name.toLowerCase().includes(query) ||
      request.student_id.toLowerCase().includes(query)
    )
  }

  // Status filter
  if (filters.value.status) {
    filtered = filtered.filter(request => request.status === filters.value.status)
  }

  // Document type filter
  if (filters.value.documentType) {
    filtered = filtered.filter(request => request.document_type === filters.value.documentType)
  }

  return filtered
})

// Table columns configuration
const tableColumns = computed(() => [
  {
    key: 'full_name',
    title: 'Alumni',
    cellClass: 'font-medium'
  },
  {
    key: 'student_id',
    title: 'Student ID',
    cellClass: 'opacity-80'
  },
  {
    key: 'program_name',
    title: 'Program',
    cellClass: 'opacity-80'
  },
  {
    key: 'document_type',
    title: 'Document Type'
  },
  {
    key: 'status',
    title: 'Status'
  },
  {
    key: 'request_date',
    title: 'Request Date'
  },
  {
    key: 'actions',
    title: 'Actions',
    cellClass: 'w-32'
  }
])

const onFiltersClear = () => {
  filters.value = {
    search: '',
    status: '',
    documentType: ''
  }
}

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

const viewRequestDetails = (request) => {
  // For now, just open the status modal - can be expanded later
  openStatusModal(request)
}

const getStatusClass = (status) => {
  return documentRequestService.getStatusClass(status)
}

const getStatusIcon = (status) => {
  const iconMap = {
    'Pending': 'fas fa-clock',
    'Processing': 'fas fa-cog',
    'Ready for Pickup': 'fas fa-clipboard-check',
    'Completed': 'fas fa-check-circle'
  }
  return iconMap[status] || 'fas fa-question-circle'
}

const getDocumentTypeIcon = (documentType) => {
  const iconMap = {
    'Transcript of Records': IconFileText,
    'Transcript of Competency': IconFileText,
    'Diploma': IconFileText,
    'Certificate of Training': IconFileText
  }
  return iconMap[documentType] || IconFileText
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
/* Custom table styling to match design system */
h1, p {
  color: var(--color-text-primary);
}

.table th {
  background: var(--color-neutral);
  border-bottom: 2px solid var(--color-border);
  padding: 1rem 0.75rem;
  font-weight: 600;
}

.table td {
  border-bottom: 1px solid var(--color-border-light);
  padding: 1rem 0.75rem;
}

.table tr:hover {
  background: rgba(var(--color-primary-rgb), 0.05);
}

.table tr:last-child td {
  border-bottom: none;
}

/* Form inputs consistent with app design */
.input, .select, .textarea {
  background: var(--color-text-invert);
  border-color: var(--color-border);
  color: var(--color-text-primary);
}

.input:focus, .select:focus, .textarea:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb), 0.1);
}

/* Modal styling */
.modal-box {
  background: var(--color-surface-main);
  color: var(--color-text-primary);
  backdrop-filter: blur(10px);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-open {
  backdrop-filter: blur(4px);
}

/* Badge styling */
.badge {
  font-weight: 500;
  font-size: 0.75rem;
}

.badge-lg {
  padding: 0.5rem 1rem;
  font-weight: 600;
  font-size: 0.875rem;
}

/* Button hover effects */
.btn:hover {
  transform: translateY(-1px);
}

/* Loading states */
.loading {
  color: var(--color-primary);
}

/* Alert styling */
.alert {
  border-radius: 0.75rem;
}

/* Status icon colors */
.fas.fa-clock {
  color: #f59e0b;
}

.fas.fa-cog {
  color: #3b82f6;
}

.fas.fa-clipboard-check {
  color: #8b5cf6;
}

.fas.fa-check-circle {
  color: #10b981;
}

/* Card hover effects */
.bg-gradient-to-r.from-blue-50:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -3px rgba(59, 130, 246, 0.1);
  transition: all 0.3s ease;
}

/* Animation */
.btn, .card, .modal-box {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom scrollbar */
.max-h-96::-webkit-scrollbar {
  width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
  background: var(--color-surface-alt);
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb {
  background: rgb(var(--color-primary-rgb) / 0.6);
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
  background: rgb(var(--color-primary-rgb) / 0.8);
}
</style>
