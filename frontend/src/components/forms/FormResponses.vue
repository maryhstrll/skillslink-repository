<template>
  <!-- Responses Modal -->
  <dialog ref="responsesModal" class="modal">
    <div class="modal-box w-11/12 max-w-5xl h-5/6 flex flex-col bg-white shadow-2xl">
      <!-- Fixed Header -->
      <div class="sticky top-0 bg-white z-10 pb-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gradient-to-r from-[#5FC9F3] to-[#2E79BA] rounded-lg flex items-center justify-center">
            <i class="fas fa-chart-bar text-white text-sm"></i>
          </div>
          <div>
            <h3 class="font-bold text-2xl text-gray-800">Form Responses</h3>
            <p class="text-gray-500 text-sm">{{ modalFormTitle }}</p>
          </div>
        </div>
      </div>
      
      <!-- Scrollable Content -->
      <div class="flex-1 overflow-y-auto py-4">
        <div v-if="responses.length" class="space-y-4">
          <div
            v-for="r in responses"
            :key="r.response_id"
            class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-100 hover:shadow-lg transition-all duration-200"
          >
            <!-- Alumni Info Header -->
            <div class="flex justify-between items-start mb-4">
              <div class="space-y-2">
                <div class="flex items-center gap-2">
                  <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-white text-xs"></i>
                  </div>
                  <div class="text-lg font-semibold text-gray-800">
                    {{ r.alumni_name || `Alumni ID: ${r.alumni_id}` }}
                  </div>
                </div>
                <div class="text-sm text-gray-600 ml-8">
                  <div>{{ r.alumni_email || 'No email available' }}</div>
                  <div>Student ID: {{ r.alumni_student_id || 'Not provided' }}</div>
                  <div class="text-xs text-gray-500 mt-1">
                    Submitted: {{ formatDate(r.submitted_at) }}
                  </div>
                </div>
              </div>
              
              <!-- Status Badges -->
              <div class="text-right space-y-2">
                <div class="badge badge-lg bg-gradient-to-r from-[#5FC9F3] to-[#2E79BA] text-white border-none">
                  {{ r.completion_percentage || 0 }}% Complete
                </div>
                <div class="flex flex-col gap-1">
                  <span v-if="r.has_form_data && r.has_employment_data" class="badge bg-green-100 text-green-800 border-green-200">
                    Complete Response
                  </span>
                  <span v-else-if="r.has_form_data" class="badge bg-yellow-100 text-yellow-800 border-yellow-200">
                    Form Only
                  </span>
                  <span v-else-if="r.has_employment_data" class="badge bg-blue-100 text-blue-800 border-blue-200">
                    Employment Only
                  </span>
                  <span v-else class="badge bg-gray-100 text-gray-800 border-gray-200">
                    Response Data
                  </span>
                </div>
              </div>
            </div>

            <!-- Response Data Section -->
            <div class="bg-white p-4 rounded-lg border border-blue-100">
              <div class="flex justify-between items-center mb-3">
                <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                  <i class="fas fa-clipboard-list text-blue-500"></i>
                  Response Data
                </h4>
                <div class="text-xs text-gray-500">
                  <span v-if="r.has_form_data && r.has_employment_data" class="text-green-600 font-medium">
                    ✓ Form + Employment Data
                  </span>
                  <span v-else-if="r.has_form_data" class="text-yellow-600 font-medium">
                    ✓ Form Data Only
                  </span>
                  <span v-else-if="r.has_employment_data" class="text-blue-600 font-medium">
                    ✓ Employment Data Only
                  </span>
                </div>
              </div>
              
              <div
                v-if="r.responses && Object.keys(r.responses).length"
                class="space-y-3"
              >
                <div
                  v-for="(answer, questionId) in getFilteredResponses(r.responses)"
                  :key="questionId"
                  class="bg-gray-50 p-3 rounded-lg border border-gray-100"
                >
                  <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">
                    {{ getQuestionText(questionId) }}
                  </div>
                  <div class="text-sm text-gray-800 font-medium">
                    {{ Array.isArray(answer) ? answer.join(", ") : answer }}
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-6">
                <i class="fas fa-inbox text-gray-300 text-2xl mb-2"></i>
                <p class="text-sm text-gray-500 italic">No response data available</p>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-12">
          <i class="fas fa-clipboard-list text-gray-300 text-4xl mb-4"></i>
          <p class="text-lg text-gray-500 font-medium">No responses yet</p>
          <p class="text-sm text-gray-400">Responses will appear here once alumni start submitting the form</p>
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
import { ref } from "vue";
import axios from "axios";

// Configure axios
axios.defaults.baseURL = "http://localhost/skillslink/backend";
axios.defaults.withCredentials = true;

// Props
const props = defineProps({
  // Optional: You can pass initial data if needed
});

// Refs
const responsesModal = ref(null);
const responses = ref([]);
const modalFormTitle = ref("");
const formQuestions = ref([]);

// Methods
const formatDate = (dateString) => {
  if (!dateString) return "No date";
  try {
    const date = new Date(dateString);
    return date.toLocaleString();
  } catch (error) {
    return dateString;
  }
};

const getQuestionText = (questionId) => {
  // Define employment-specific question labels
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
  };
  
  // Check if it's an employment question first
  if (employmentQuestions[questionId]) {
    return employmentQuestions[questionId];
  }
  
  // Then check form questions
  const question = formQuestions.value.find(q => q.id == questionId);
  return question ? question.label : `Question: ${questionId}`;
};

const getFilteredResponses = (responses) => {
  if (!responses || typeof responses !== 'object') {
    return {};
  }
  
  const filtered = {};
  Object.entries(responses).forEach(([key, value]) => {
    if (value && value !== '' && value !== 'null' && value !== null) {
      filtered[key] = value;
    }
  });
  
  return filtered;
};

const fetchFormQuestions = async (formId) => {
  try {
    const res = await axios.get(`/admin/tracer_forms.php?action=get&form_id=${formId}`);
    if (res.data && res.data.form_questions) {
      // Handle both string and array formats
      if (typeof res.data.form_questions === 'string') {
        formQuestions.value = JSON.parse(res.data.form_questions);
      } else {
        formQuestions.value = res.data.form_questions;
      }
    } else {
      formQuestions.value = [];
    }
  } catch (err) {
    console.error("Error fetching form questions:", err);
    formQuestions.value = [];
  }
};

const viewResponses = async (item) => {
  modalFormTitle.value = item.form_title;
  responses.value = [];
  formQuestions.value = [];
  
  try {
    // Fetch form questions first
    await fetchFormQuestions(item.form_id);
    
    // Then fetch responses
    const res = await axios.get(
      `/admin/form_responses.php?action=list&form_id=${item.form_id}`
    );
    console.log("Responses data:", res.data); // Debug log
    console.log("Form questions:", formQuestions.value); // Debug log
    responses.value = res.data || [];
    responsesModal.value.showModal();
  } catch (err) {
    console.error("Error fetching responses:", err);
    alert("Failed to fetch responses");
  }
};

// Expose methods to parent component
defineExpose({
  viewResponses
});
</script>

<style scoped>
/* Ensure consistent white backgrounds */
.modal-box {
  background: white !important;
  color: #1f2937;
}

/* Badge styling to match the design system */
.badge {
  font-weight: 500;
  font-size: 0.75rem;
  padding: 0.25rem 0.75rem;
}

.badge-lg {
  padding: 0.5rem 1rem;
  font-weight: 600;
  font-size: 0.875rem;
}

/* Card hover effects */
.bg-gradient-to-r.from-blue-50:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -3px rgba(59, 130, 246, 0.1);
  transition: all 0.3s ease;
}

/* Button hover effects */
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
.btn, .badge, .bg-gradient-to-r {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
