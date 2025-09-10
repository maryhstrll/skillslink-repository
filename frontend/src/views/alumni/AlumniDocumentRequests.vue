<template>
  <Layout @logout="handleLogout">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 class="text-3xl font-bold text-base-content">My Document Requests</h1>
            <p class="text-base-content/70 mt-1">
              Request and track official documents from your alma mater
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
            <button 
              class="btn btn-accent-custom shadow-lg hover:shadow-xl transition-all duration-200"
              @click="openRequestModal"
              :disabled="loading">
              <IconPlus class="w-4 h-4" />
              New Request
            </button>
          </div>
        </div>

        <!-- Document Requests DataTable -->
        <DataTable
          title="My Document Requests"
          :title-icon="IconFileText"
          :data="requests"
          :columns="tableColumns"
          :loading="loading"
          item-label="requests"
          empty-title="No document requests yet"
          empty-description="Click 'New Request' to submit your first document request"
          :empty-icon="IconFileText"
          key-field="request_id"
          loading-text="Loading your document requests..."
        >
          <!-- Custom cell for document type with icon -->
          <template #cell-document_type="{ value }">
            <div class="flex items-center gap-2">
              <IconFileText class="w-4 h-4" style="color: var(--color-primary);" />
              <span>{{ value }}</span>
            </div>
          </template>

          <!-- Custom cell for purpose -->
          <template #cell-purpose="{ value }">
            <span style="color: var(--color-text-primary);">{{ value || 'Not specified' }}</span>
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
            <span class="text-sm" style="color: var(--color-text-secondary);">
              {{ formatDate(value) }}
            </span>
          </template>

          <!-- Custom cell for last updated -->
          <template #cell-updated_at="{ value }">
            <span class="text-sm" style="color: var(--color-text-secondary);">
              {{ formatDate(value) }}
            </span>
          </template>
        </DataTable>
      </div>

    <!-- New Request Modal -->
    <div v-if="showModal" class="modal modal-open">
      <div class="modal-box bg-white relative max-w-2xl shadow-2xl">
        <!-- Close Button -->
        <button 
          class="btn btn-sm btn-circle absolute right-4 top-4 bg-gray-100 hover:bg-gray-200 border-none text-gray-600 hover:text-gray-800 transition-colors" 
          @click="closeModal"
        >
          ✕
        </button>
        
        <!-- Header -->
        <div class="border-b border-gray-200 pb-4 mb-6">
          <h3 class="font-bold text-2xl text-gray-800 flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
              <IconPlus class="w-5 h-5 text-white" />
            </div>
            Request Document
          </h3>
          <p class="text-gray-500 mt-2">Submit a request for official academic documents</p>
        </div>
        
        <form @submit.prevent="submitRequest" class="space-y-6">
          <!-- Document Type Selection -->
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-100">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <IconFileText class="w-4 h-4 text-white" />
              </div>
              <h4 class="font-semibold text-gray-800 text-lg">Document Selection</h4>
            </div>
            
            <div class="form-control">
              <label class="label">
                <span class="label-text text-gray-700 font-medium">Document Type *</span>
                <span class="label-text-alt text-red-500">Required</span>
              </label>
              <select 
                v-model="form.document_type" 
                class="select select-bordered w-full bg-white text-gray-800 border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors" 
                required
              >
                <option value="" disabled>Select document type</option>
                <option value="Transcript of Records" class="flex items-center">
                  📊 Transcript of Records
                </option>
                <option value="Transcript of Competency" class="flex items-center">
                  📋 Transcript of Competency
                </option>
                <option value="Diploma" class="flex items-center">
                  🎓 Diploma
                </option>
                <option value="Certificate of Training" class="flex items-center">
                  📜 Certificate of Training
                </option>
              </select>
              <div class="label">
                <span class="label-text-alt text-gray-500">Choose the type of document you need</span>
              </div>
            </div>
          </div>

          <!-- Purpose Section -->
          <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <IconFileText class="w-4 h-4 text-white" />
              </div>
              <h4 class="font-semibold text-gray-800 text-lg">Request Details</h4>
            </div>
            
            <div class="form-control">
              <label class="label">
                <span class="label-text text-gray-700 font-medium">Purpose</span>
                <span class="label-text-alt text-gray-500">Optional</span>
              </label>
              <textarea 
                v-model="form.purpose" 
                class="textarea textarea-bordered w-full bg-white text-gray-800 border-2 border-gray-200 focus:border-green-500 focus:outline-none transition-colors min-h-[100px]" 
                placeholder="Briefly describe the purpose of this request (e.g., for job application, further studies, etc.)"
                rows="4"
              ></textarea>
              <div class="label">
                <span class="label-text-alt text-gray-500">Providing a purpose helps us prioritize your request</span>
              </div>
            </div>
          </div>

          <!-- Important Notice -->
          <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
              <div class="w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                <IconInfo class="w-3 h-3 text-white" />
              </div>
              <div>
                <h5 class="font-medium text-amber-800 mb-1">Processing Information</h5>
                <p class="text-amber-700 text-sm leading-relaxed">
                  Document requests typically take 3-5 business days to process. You will be notified via email when your document is ready for pickup or when the status changes.
                </p>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <button 
              type="button" 
              class="btn btn-outline px-6 py-2 border-gray-300 text-gray-600 hover:bg-gray-50 hover:border-gray-400 transition-colors" 
              @click="closeModal"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="btn btn-primary-custom px-6 py-2 shadow-lg hover:shadow-xl transition-all duration-200" 
              :disabled="submitting || !form.document_type"
              :class="{ 'opacity-50 cursor-not-allowed': submitting || !form.document_type }"
            >
              <span v-if="submitting" class="loading loading-spinner loading-sm mr-2"></span>
              {{ submitting ? 'Submitting...' : 'Submit Request' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import Layout from '@/components/layout/Layout.vue'
import DataTable from '@/components/tables/DataTable.vue'
import { useRouter } from 'vue-router'
import documentRequestService from '@/services/documentRequest.js'
import { messageService } from '@/services/messageService.js'
import { 
  FileText as IconFileText,
  Plus as IconPlus,
  RefreshCw as IconRefreshCw,
  Info as IconInfo
} from 'lucide-vue-next'

const router = useRouter()

// State
const requests = ref([])
const loading = ref(false)
const showModal = ref(false)
const submitting = ref(false)

// Form data
const form = reactive({
  document_type: '',
  purpose: ''
})

// Table columns configuration
const tableColumns = computed(() => [
  {
    key: 'document_type',
    title: 'Document Type',
    cellClass: 'font-medium'
  },
  {
    key: 'purpose',
    title: 'Purpose',
    cellClass: 'max-w-xs'
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
    key: 'updated_at',
    title: 'Last Updated'
  }
])

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

const openRequestModal = () => {
  // Reset form
  form.document_type = ''
  form.purpose = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const submitRequest = async () => {
  try {
    submitting.value = true
    
    const result = await documentRequestService.create(form)
    
    if (result.success) {
      messageService.toast('Document request submitted successfully!', 'success')
      closeModal()
      await fetchRequests() // Refresh the list
    } else {
      messageService.alert(result.message || 'Failed to submit request', 'error')
    }
  } catch (error) {
    console.error('Error submitting request:', error)
    messageService.alert('Failed to submit request. Please try again.', 'error')
  } finally {
    submitting.value = false
  }
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

/* Button hover effects */
.btn:hover {
  transform: translateY(-1px);
}

/* Loading states */
.loading {
  color: var(--color-primary);
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
