<template>
  <Layout @logout="handleLogout">
    <div class="min-h-screen bg-gradient-to-br from-[#081F37] to-[#1E549F] p-4 md:p-6 lg:p-8">
      <div class="max-w-6xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 shadow-xl border border-white/20">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="text-white">
              <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-2 bg-gradient-to-r from-[#5FC9F3] to-[#2E79BA] bg-clip-text text-transparent">
                Document Requests
              </h1>
              <p class="text-white/80 text-sm md:text-base">Request official documents from your alma mater</p>
            </div>
            <button 
              class="btn bg-[#5FC9F3] text-white border-none hover:bg-[#2E79BA] hover:scale-105 transition-all duration-300"
              @click="openRequestModal"
              :disabled="loading">
              <i class="fas fa-plus w-4 h-4"></i>
              New Request
            </button>
          </div>
        </div>

        <!-- Document Requests List -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg shadow-xl border border-white/20 overflow-hidden">
          <div v-if="loading" class="flex justify-center items-center py-12">
            <span class="loading loading-spinner loading-lg text-white"></span>
          </div>
          
          <div v-else-if="requests.length === 0" class="text-center py-12 text-white">
            <i class="fas fa-file-alt text-6xl mb-4 text-white/30"></i>
            <p class="text-xl mb-2">No document requests yet</p>
            <p class="text-white/70">Click "New Request" to submit your first document request</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="table w-full">
              <thead class="bg-[#2E79BA]/20 text-white">
                <tr>
                  <th class="text-white">Document Type</th>
                  <th class="text-white">Purpose</th>
                  <th class="text-white">Status</th>
                  <th class="text-white">Request Date</th>
                  <th class="text-white">Last Updated</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="request in requests" :key="request.request_id" class="hover:bg-white/5 text-white">
                  <td>
                    <div class="flex items-center gap-3">
                      <i :class="getDocumentTypeIcon(request.document_type)" class="text-[#5FC9F3]"></i>
                      <span class="font-medium">{{ request.document_type }}</span>
                    </div>
                  </td>
                  <td class="text-white/80">
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
                  <td class="text-white/80">
                    {{ formatDate(request.updated_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- New Request Modal -->
    <div v-if="showModal" class="modal modal-open">
      <div class="modal-box bg-white relative">
        <button class="btn btn-sm btn-circle absolute right-2 top-2" @click="closeModal">✕</button>
        <h3 class="font-bold text-lg text-gray-800 mb-4">Request Document</h3>
        
        <form @submit.prevent="submitRequest" class="space-y-4">
          <div class="form-control">
            <label class="label">
              <span class="label-text text-gray-700">Document Type *</span>
            </label>
            <select v-model="form.document_type" class="select select-bordered w-full bg-white text-gray-800" required>
              <option value="">Select document type</option>
              <option value="Transcript of Records">Transcript of Records</option>
              <option value="Transcript of Competency">Transcript of Competency</option>
              <option value="Diploma">Diploma</option>
              <option value="Certificate of Training">Certificate of Training</option>
            </select>
          </div>

          <div class="form-control">
            <label class="label">
              <span class="label-text text-gray-700">Purpose</span>
            </label>
            <textarea 
              v-model="form.purpose" 
              class="textarea textarea-bordered w-full bg-white text-gray-800" 
              placeholder="Briefly describe the purpose of this request (optional)"
              rows="3">
            </textarea>
          </div>

          <div class="modal-action">
            <button type="button" class="btn btn-outline" @click="closeModal">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="submitting">
              <span v-if="submitting" class="loading loading-spinner loading-sm"></span>
              {{ submitting ? 'Submitting...' : 'Submit Request' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import Layout from '@/components/Layout.vue'
import { useRouter } from 'vue-router'
import documentRequestService from '@/services/documentRequest.js'
import { messageService } from '@/services/messageService.js'

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
</style>
