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
            <div class="w-10 h-10 bg-gradient-to-r from-[#5FC9F3] to-[#2E79BA] rounded-lg flex items-center justify-center">
              <i class="fas fa-file-plus text-white text-sm"></i>
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
                <i class="fas fa-file-alt text-white text-sm"></i>
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
                <i class="fas fa-comment-alt text-white text-sm"></i>
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
                <i class="fas fa-info text-white text-xs"></i>
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
              <i class="fas fa-times mr-2"></i>
              Cancel
            </button>
            <button 
              type="submit" 
              class="btn bg-gradient-to-r from-[#5FC9F3] to-[#2E79BA] text-white border-none hover:from-[#2E79BA] hover:to-[#1a5490] px-6 py-2 shadow-lg hover:shadow-xl transition-all duration-200" 
              :disabled="submitting || !form.document_type"
              :class="{ 'opacity-50 cursor-not-allowed': submitting || !form.document_type }"
            >
              <span v-if="submitting" class="loading loading-spinner loading-sm mr-2"></span>
              <i v-else class="fas fa-paper-plane mr-2"></i>
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

/* Modal enhancements */
.modal-box {
  backdrop-filter: blur(10px);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-open {
  backdrop-filter: blur(4px);
}

/* Form styling */
.select:focus,
.textarea:focus {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Button hover effects */
.btn:not(:disabled):hover {
  transform: translateY(-1px);
}

/* Gradient text animation */
@keyframes gradient {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.bg-gradient-to-r {
  background-size: 200% 200%;
  animation: gradient 3s ease infinite;
}

/* Card hover effects */
.bg-gradient-to-r.from-blue-50:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -3px rgba(59, 130, 246, 0.1);
  transition: all 0.3s ease;
}

/* Loading animation improvements */
.loading-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
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

/* Form validation styling */
.select:invalid {
  border-color: #ef4444;
}

.select:valid {
  border-color: #10b981;
}

/* Textarea enhancements */
.textarea {
  resize: vertical;
  min-height: 100px;
}

.textarea:focus {
  border-color: #10b981;
}

/* Notice box animations */
.bg-amber-50 {
  animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Label styling improvements */
.label-text-alt {
  font-size: 0.75rem;
  font-weight: 500;
}

/* Icon styling */
.fas {
  transition: transform 0.2s ease;
}

.btn:hover .fas {
  transform: scale(1.1);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .modal-box {
    margin: 1rem;
    max-width: calc(100vw - 2rem);
  }
  
  .grid-cols-2 {
    grid-template-columns: 1fr;
  }
}
</style>
