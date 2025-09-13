<template>
  <dialog ref="responseModal" class="modal">
    <div class="modal-box w-11/12 max-w-4xl h-5/6 flex flex-col bg-white shadow-2xl">
      <!-- Header -->
      <div class="sticky top-0 bg-white z-10 pb-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gradient-to-r from-[#5FC9F3] to-[#2E79BA] rounded-lg flex items-center justify-center">
            <i class="fas fa-user text-white text-sm"></i>
          </div>
          <div>
            <h3 class="font-bold text-2xl text-gray-800">Response Details</h3>
            <p class="text-gray-500 text-sm">{{ formTitle }}</p>
          </div>
        </div>
      </div>

      <!-- Scrollable Content -->
      <div class="flex-1 overflow-y-auto py-4">
        <div v-if="response" class="space-y-6">
          <!-- Alumni Information -->
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-100">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-white text-sm"></i>
              </div>
              <h4 class="font-semibold text-gray-800 text-lg">Alumni Information</h4>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-3">
                <div class="flex flex-col">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Name</span>
                  <span class="text-gray-800 font-medium">{{ response.alumni_name || 'N/A' }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Student ID</span>
                  <span class="text-gray-800 font-mono">{{ response.alumni_student_id || 'N/A' }}</span>
                </div>
              </div>
              
              <div class="space-y-3">
                <div class="flex flex-col">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</span>
                  <span class="text-gray-800">{{ response.alumni_email || 'N/A' }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Submitted</span>
                  <span class="text-gray-800">{{ formatDate(response.submitted_at) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Response Data -->
          <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <i class="fas fa-clipboard-list text-white text-sm"></i>
              </div>
              <h4 class="font-semibold text-gray-800 text-lg">Response Data</h4>
            </div>
            
            <div v-if="response.responses && Object.keys(response.responses).length" class="space-y-3">
              <div
                v-for="(answer, questionId) in getFilteredResponses(response.responses)"
                :key="questionId"
                class="bg-white p-4 rounded-lg border border-gray-100"
              >
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                  {{ getQuestionText(questionId) }}
                </div>
                <div class="text-gray-800 font-medium">
                  {{ Array.isArray(answer) ? answer.join(", ") : answer }}
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <i class="fas fa-inbox text-gray-300 text-3xl mb-3"></i>
              <p class="text-gray-500">No response data available</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Fixed Footer -->
      <div class="sticky bottom-0 bg-white z-10 pt-4 border-t border-gray-200">
        <div class="modal-action mt-0 flex justify-end">
          <form method="dialog">
            <button class="btn btn-outline px-6 py-2 border-gray-300 text-gray-600 hover:bg-gray-50 hover:border-gray-400 transition-colors">
              <i class="fas fa-times mr-2"></i>
              Close
            </button>
          </form>
        </div>
      </div>
    </div>
  </dialog>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

// Configure axios
axios.defaults.baseURL = "http://localhost/skillslink/backend"
axios.defaults.withCredentials = true

// Refs
const responseModal = ref(null)
const response = ref(null)
const formTitle = ref('')
const formQuestions = ref([])

// Methods
const formatDate = (dateString) => {
  if (!dateString) return "No date"
  try {
    const date = new Date(dateString)
    return date.toLocaleString()
  } catch (error) {
    return dateString
  }
}

const getQuestionText = (questionId) => {
  // Employment-specific question labels
  const employmentQuestions = {
    'employment_status': 'Employment Status',
    'company_name': 'Company Name',
    'occupation': 'Occupation/Job Title',
    'job_description': 'Job Description',
    'salary_range': 'Salary Range',
    'work_classification': 'Work Classification',
    'company_size': 'Company Size',
    'industry': 'Industry',
    'work_location': 'Work Location',
    'is_local': 'Is Local Employment',
    'is_abroad': 'Is Employment Abroad',
    'employment_type': 'Employment Type/Nature',
    'date_employed': 'Date Employed',
    'job_relevance_to_course': 'Job Relevance to Course',
    'skills_used': 'Skills Used',
    'months_to_find_job': 'Months to Find Job',
    'job_search_method': 'Job Search Method',
    'additional_comments': 'Additional Comments'
  }
  
  if (employmentQuestions[questionId]) {
    return employmentQuestions[questionId]
  }
  
  const question = formQuestions.value.find(q => q.id == questionId)
  return question ? question.label : `Question: ${questionId}`
}

const getFilteredResponses = (responses) => {
  if (!responses || typeof responses !== 'object') {
    return {}
  }
  
  const filtered = {}
  Object.entries(responses).forEach(([key, value]) => {
    if (value && value !== '' && value !== 'null' && value !== null) {
      filtered[key] = value
    }
  })
  
  return filtered
}

const fetchFormQuestions = async (formId) => {
  try {
    const res = await axios.get(`/admin/tracer_forms.php?action=get&form_id=${formId}`)
    if (res.data && res.data.form_questions) {
      if (typeof res.data.form_questions === 'string') {
        formQuestions.value = JSON.parse(res.data.form_questions)
      } else {
        formQuestions.value = res.data.form_questions
      }
    } else {
      formQuestions.value = []
    }
  } catch (err) {
    console.error("Error fetching form questions:", err)
    formQuestions.value = []
  }
}

const viewResponse = async (responseData, formData) => {
  response.value = responseData
  formTitle.value = formData.form_title
  formQuestions.value = []
  
  try {
    await fetchFormQuestions(formData.form_id)
    responseModal.value.showModal()
  } catch (err) {
    console.error("Error loading response:", err)
    alert("Failed to load response details")
  }
}

// Expose methods
defineExpose({
  viewResponse
})
</script>

<style scoped>
/* Same styling as FormResponses.vue */
.modal-box {
  background: white !important;
  color: #1f2937;
}

.badge {
  font-weight: 500;
  font-size: 0.75rem;
  padding: 0.25rem 0.75rem;
}

.bg-gradient-to-r.from-blue-50:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -3px rgba(59, 130, 246, 0.1);
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-1px);
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #5FC9F3;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #2E79BA;
}

/* Animation */
.btn, .bg-gradient-to-r {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>