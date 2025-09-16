<template>
  <Layout @logout="handleLogout">
    <div class="p-6 max-w-4xl mx-auto">
      <!-- Page Header -->
      <div class="mb-8 text-center">
        <div class="flex flex-col items-center gap-4 mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-xl">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 002 2h4a2 2 0 002-2V4"></path>
            </svg>
          </div>
          <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Employment Tracer Form</h1>
            <p class="text-lg text-gray-600 max-w-2xl">Share your employment information and career journey with your alma mater to help us better serve our alumni community</p>
          </div>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="text-center">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mb-4"></div>
          <p class="text-gray-600 font-medium">Loading form...</p>
        </div>
      </div>

      <div v-else>
        <!-- Form Description -->
        <div v-if="form_description" class="mb-8 bg-white p-6 rounded-xl border border-gray-200 shadow-lg">
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="font-semibold text-gray-900 text-lg mb-2">Form Information</h3>
              <p class="text-gray-700 leading-relaxed mb-4">{{ form_description }}</p>
              <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V7a1 1 0 011-1h4a1 1 0 011 1v0m-6 0h6"></path>
                </svg>
                Form Year: {{ form_year }}
              </div>
            </div>
          </div>
        </div>

        <!-- Already Responded Message -->
        <div v-if="alreadyResponded" class="space-y-4 mb-6">
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 p-6 rounded-xl shadow-lg">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 class="font-semibold text-green-800 text-lg">Response Submitted</h3>
            </div>
            <p class="text-green-700 mb-4">{{ alreadyMessage }}</p>
          </div>
          
          <div class="flex gap-4">
            <button 
              @click="showExistingResponses = !showExistingResponses"
              class="btn btn-outline border-gray-300 text-gray-600 hover:bg-gray-50 hover:border-gray-400 transition-colors px-6 py-2"
            >
              <svg v-if="!showExistingResponses" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.05 6.05m3.828 3.828l4.242 4.242M9.878 9.878L13.12 13.12"></path>
              </svg>
              {{ showExistingResponses ? 'Hide' : 'View' }} My Responses
            </button>
          </div>
        </div>

        <!-- Show existing responses (view mode) -->
        <div v-if="showExistingResponses" class="space-y-6 mb-6">
          <!-- Employment Data -->
          <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-lg">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 002 2h4a2 2 0 002-2V4"></path>
                </svg>
              </div>
              <h3 class="font-semibold text-gray-800 text-lg">Your Employment Information</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="question in coreQuestions" :key="question.id" 
                   v-show="existingEmploymentData[question.maps_to]" class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">{{ question.label }}</div>
                <div class="text-gray-800 font-medium">{{ formatValue(existingEmploymentData[question.maps_to]) }}</div>
              </div>
            </div>
          </div>

          <!-- Additional Responses -->
          <div v-if="Object.keys(existingAdditionalData).length" class="bg-white p-6 rounded-xl border border-gray-200 shadow-lg">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
              <h3 class="font-semibold text-gray-800 text-lg">Additional Survey Responses</h3>
            </div>
            <div class="space-y-3">
              <div v-for="(answer, questionId) in existingAdditionalData" :key="questionId" 
                   class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">{{ getQuestionLabel(questionId) }}</div>
                <div class="text-gray-800 font-medium">{{ Array.isArray(answer) ? answer.join(", ") : answer }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form to fill out -->
        <div v-if="!alreadyResponded">
          <form @submit.prevent="submitForm" class="space-y-6">
            <!-- Core Employment Section -->
            <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-lg">
              <div class="flex items-start gap-4 mb-8 pb-6 border-b border-gray-100">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 002 2h4a2 2 0 002-2V4"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="text-2xl font-bold text-gray-900 mb-2">Employment Information</h2>
                  <p class="text-gray-600">Please provide your current employment details and career status</p>
                </div>
              </div>
                
                <div class="space-y-6">
                  <div v-for="question in coreQuestions" :key="question.id" 
                       v-show="shouldShowQuestion(question)" 
                       class="space-y-2">
                    <label class="block">
                      <span class="text-sm font-medium text-gray-700 mb-2 block">
                        {{ question.label }}
                        <span class="text-red-500">*</span>
                      </span>
                    </label>

                    <!-- Radio buttons -->
                    <div v-if="question.type === 'radio'" class="space-y-3">
                      <label v-for="option in question.options" :key="option" 
                             class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-3 rounded-lg border border-gray-200 transition-colors">
                        <input type="radio" 
                               :name="question.id"
                               :value="question.id === 'employment_status' ? option.toLowerCase().replace(/ /g, '_') : 
                                      (question.id === 'work_classification' || question.id === 'employment_type') ? option : 
                                      option.toLowerCase().replace(/ /g, '_')"
                               v-model="employmentData[question.maps_to]"
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2" />
                        <span class="text-gray-800 font-medium">{{ option }}</span>
                      </label>
                    </div>

                    <!-- Text inputs -->
                    <input v-else-if="question.type === 'text'"
                           type="text"
                           v-model="employmentData[question.maps_to]"
                           :placeholder="question.placeholder || 'Enter ' + question.label.toLowerCase()"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white text-gray-800" />

                    <!-- Date inputs -->
                    <input v-else-if="question.type === 'date'"
                           type="date"
                           v-model="employmentData[question.maps_to]"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white text-gray-800" />

                    <!-- Textarea -->
                    <textarea v-else-if="question.type === 'textarea'"
                              v-model="employmentData[question.maps_to]"
                              :placeholder="question.placeholder || 'Enter ' + question.label.toLowerCase()"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white text-gray-800"
                              rows="3"></textarea>

                    <!-- Number inputs -->
                    <input v-else-if="question.type === 'number'"
                           type="number"
                           v-model="employmentData[question.maps_to]"
                           :min="question.min"
                           :max="question.max"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white text-gray-800" />

                    <!-- Select dropdowns -->
                    <select v-else-if="question.type === 'select'"
                            v-model="employmentData[question.maps_to]"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white text-gray-800">
                      <option disabled value="" class="text-gray-500">Select an option</option>
                      <option v-for="option in question.options" :key="option" 
                              :value="question.id === 'salary_range' ? option : option.toLowerCase().replace(/[^a-z0-9]/g, '_')"
                              class="text-gray-800">
                        {{ option }}
                      </option>
                    </select>
                  </div>
                </div>
            </div>

            <!-- Additional Questions Section -->
            <div v-if="additionalQuestions.length" class="bg-white p-8 rounded-xl border border-gray-200 shadow-lg">
              <div class="flex items-start gap-4 mb-8 pb-6 border-b border-gray-100">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center flex-shrink-0">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="text-2xl font-bold text-gray-900 mb-2">Additional Survey Questions</h2>
                  <p class="text-gray-600">Please answer the following questions to help us better understand your experience</p>
                </div>
              </div>
                
                <div class="space-y-6">
                  <div v-for="question in additionalQuestions" :key="question.id" class="space-y-2">
                    <label class="block">
                      <span class="text-sm font-medium text-gray-700 mb-2 block">
                        {{ question.label }}
                        <span v-if="question.required" class="text-red-500">*</span>
                      </span>
                    </label>

                    <input v-if="question.type === 'text'"
                           type="text"
                           v-model="additionalData[question.id]"
                           :placeholder="question.placeholder"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white text-gray-800" />

                    <textarea v-else-if="question.type === 'textarea'"
                              v-model="additionalData[question.id]"
                              :placeholder="question.placeholder"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white text-gray-800"
                              rows="3"></textarea>

                    <input v-else-if="question.type === 'number'"
                           type="number"
                           v-model="additionalData[question.id]"
                           :min="question.min"
                           :max="question.max"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white text-gray-800" />

                    <div v-else-if="question.type === 'radio'" class="space-y-3">
                      <label v-for="option in question.options" :key="option"
                             class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-3 rounded-lg border border-gray-200 transition-colors">
                        <input type="radio" 
                               :name="question.id"
                               :value="option"
                               v-model="additionalData[question.id]"
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2" />
                        <span class="text-gray-800 font-medium">{{ option }}</span>
                      </label>
                    </div>

                    <div v-else-if="question.type === 'checkbox'" class="space-y-3">
                      <label v-for="option in question.options" :key="option"
                             class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-3 rounded-lg border border-gray-200 transition-colors">
                        <input type="checkbox" 
                               :value="option"
                               @change="handleCheckboxChange(question.id, option, $event)"
                               :checked="additionalData[question.id] && additionalData[question.id].includes(option)"
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" />
                        <span class="text-gray-800 font-medium">{{ option }}</span>
                      </label>
                    </div>

                    <select v-else-if="question.type === 'select'"
                            v-model="additionalData[question.id]"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white text-gray-800">
                      <option disabled value="" class="text-gray-500">Select an option</option>
                      <option v-for="option in question.options" :key="option" :value="option" class="text-gray-800">
                        {{ option }}
                      </option>
                    </select>
                  </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-4 pt-8 mt-8 border-t border-gray-200">
              <button 
                type="submit" 
                class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold px-8 py-4 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none text-base flex items-center gap-3" 
                :disabled="!isFormValid() || submitting"
              >
                <svg v-if="submitting" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                {{ submitting ? 'Submitting Employment Tracer...' : 'Submit Employment Tracer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import Layout from "@/components/layout/Layout.vue";
import { useRouter } from "vue-router";
import axios from "axios";

axios.defaults.baseURL = "http://localhost/skillslink/backend";
axios.defaults.withCredentials = true;

const router = useRouter();
const loading = ref(true);
const submitting = ref(false);
const form_description = ref("");
const form_year = ref(null);
const coreQuestions = ref([]);
const additionalQuestions = ref([]);
const alreadyResponded = ref(false);
const alreadyMessage = ref("");
const showExistingResponses = ref(false);

// Separate data objects
const employmentData = reactive({
  employment_status: '',
  company_name: '',
  job_title: '',
  job_description: '',
  salary_range: '',
  employment_type: '',
  work_classification: '',
  industry: '',
  work_location: '',
  is_local: '',
  is_abroad: '',
  date_employed: '',
  months_to_find_job: null
});

const additionalData = reactive({});
const existingEmploymentData = ref({});
const existingAdditionalData = ref({});

const shouldShowQuestion = (question) => {
  if (!question.conditional) return true;
  
  // Simple conditional logic
  if (question.conditional === 'employment_status == "employed"') {
    return employmentData.employment_status === 'employed';
  }
  
  return true;
};

const validateForm = () => {
  const missingFields = [];
  
  // Check all enabled core employment questions are filled
  for (const question of coreQuestions.value) {
    if (shouldShowQuestion(question)) {
      const value = employmentData[question.maps_to];
      
      // Special handling for number fields - they can be 0
      if (question.type === 'number') {
        if (value === null || value === undefined || value === '') {
          missingFields.push(question.label);
        }
      } else {
        if (!value || value === '') {
          missingFields.push(question.label);
        }
      }
    }
  }
  
  // Check required additional questions
  for (const question of additionalQuestions.value) {
    if (question.required) {
      const value = additionalData[question.id];
      if (!value || (Array.isArray(value) && value.length === 0) || value === '') {
        missingFields.push(question.label);
      }
    }
  }
  
  return {
    isValid: missingFields.length === 0,
    missingFields: missingFields
  };
};

// Keep the old function for button state (always return true to make button clickable)
const isFormValid = () => {
  return true;
};

const formatValue = (value) => {
  if (!value) return 'Not provided';
  
  // Convert underscores to spaces and capitalize
  let formatted = value.toString().replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
  
  // Format dates nicely
  if (value.match(/^\d{4}-\d{2}-\d{2}$/)) {
    try {
      const date = new Date(value);
      formatted = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    } catch (e) {
      // If date parsing fails, return the original formatted value
    }
  }
  
  return formatted;
};

const getQuestionLabel = (questionId) => {
  if (!Array.isArray(additionalQuestions.value)) {
    return questionId;
  }
  const question = additionalQuestions.value.find(q => q.id === questionId);
  return question ? question.label : questionId;
};

const loadForm = async () => {
  try {
    const { data } = await axios.get("/alumni/get_employment_tracer.php");
    
    form_description.value = data.form_description || "";
    form_year.value = data.form_year;
    coreQuestions.value = data.core_questions || [];
    additionalQuestions.value = data.additional_questions || [];
    
    if (data.already_responded) {
      alreadyResponded.value = true;
      alreadyMessage.value = data.message;
      existingEmploymentData.value = data.existing_employment_data || {};
      existingAdditionalData.value = data.existing_additional_data || {};
    } else {
      // Initialize data for new form
      if (Array.isArray(additionalQuestions.value)) {
        additionalQuestions.value.forEach(q => {
          additionalData[q.id] = q.type === 'checkbox' ? [] : '';
        });
      }
    }
    
  } catch (err) {
    console.error("Error loading form:", err);
    alert("Error loading employment tracer form");
  } finally {
    loading.value = false;
  }
};

const handleCheckboxChange = (questionId, option, event) => {
  if (!additionalData[questionId]) {
    additionalData[questionId] = [];
  }
  
  if (event.target.checked) {
    if (!additionalData[questionId].includes(option)) {
      additionalData[questionId].push(option);
    }
  } else {
    const index = additionalData[questionId].indexOf(option);
    if (index > -1) {
      additionalData[questionId].splice(index, 1);
    }
  }
};

const submitForm = async () => {
  const validation = validateForm();
  
  if (!validation.isValid) {
    const missingFieldsList = validation.missingFields.map(field => `• ${field}`).join('\n');
    alert(`Please fill in the following required fields:\n\n${missingFieldsList}`);
    return;
  }
  
  try {
    submitting.value = true;
    
    const payload = {
      employment_data: { ...employmentData },
      additional_responses: { ...additionalData }
    };
    
    console.log("Submitting payload:", payload);
    
    const response = await axios.post("/alumni/submit_employment_tracer.php", payload);
    
    if (response.data.success) {
      alert("Employment tracer submitted successfully!");
      
      // Update state after successful submission
      alreadyResponded.value = true;
      alreadyMessage.value = `You have successfully submitted your employment tracer form for ${form_year.value}.`;
      existingEmploymentData.value = { ...employmentData };
      existingAdditionalData.value = { ...additionalData };
      showExistingResponses.value = false;
    } else {
      alert(response.data.message || "Failed to submit employment tracer");
    }
    
  } catch (err) {
    console.error("Error submitting form:", err);
    alert("Error submitting employment tracer form: " + (err.response?.data?.message || err.message));
  } finally {
    submitting.value = false;
  }
};

onMounted(loadForm);

const handleLogout = () => {
  router.push("/home");
};
</script>

<style scoped>
/* Custom styling to match design system */
h1, h2, h3, p, span {
  color: rgb(31, 41, 55); /* text-gray-800 */
}

/* Enhanced button styling for better visibility */
button[type="submit"] {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgb(37, 99, 235);
  position: relative;
  overflow: hidden;
}

button[type="submit"]:before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

button[type="submit"]:hover:before {
  left: 100%;
}

button[type="submit"]:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -3px rgba(59, 130, 246, 0.4), 0 4px 6px -2px rgba(59, 130, 246, 0.05);
}

button[type="submit"]:active {
  transform: translateY(0);
}

button[type="submit"]:disabled {
  transform: none;
  box-shadow: none;
}

/* Form input enhancements */
input:focus, textarea:focus, select:focus {
  outline: none;
  border-color: rgb(59, 130, 246);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  transform: translateY(-1px);
}

/* Radio button enhancements */
input[type="radio"]:checked {
  background-color: rgb(59, 130, 246);
  border-color: rgb(59, 130, 246);
}

input[type="checkbox"]:checked {
  background-color: rgb(59, 130, 246);
  border-color: rgb(59, 130, 246);
}

/* Card animations */
.bg-white {
  transition: all 0.3s ease;
}

.bg-white:hover {
  transform: translateY(-1px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Radio and checkbox hover effects */
input[type="radio"], input[type="checkbox"] {
  transition: all 0.2s ease;
}

label:hover input[type="radio"], 
label:hover input[type="checkbox"] {
  border-color: rgb(59, 130, 246);
}

/* Enhanced gradient backgrounds */
.bg-gradient-to-br {
  background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}

.bg-gradient-to-r {
  background-image: linear-gradient(to right, var(--tw-gradient-stops));
}

/* Loading animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Section header improvements */
h2 {
  letter-spacing: -0.025em;
}

/* Form section spacing */
.space-y-6 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 1.5rem;
}

/* Better focus outlines */
*:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .p-8 {
    padding: 1.5rem;
  }
  
  .text-4xl {
    font-size: 2rem;
  }
  
  .text-2xl {
    font-size: 1.5rem;
  }
  
  button[type="submit"] {
    width: 100%;
    justify-content: center;
  }
}

/* Print styles */
@media print {
  .bg-gradient-to-br,
  .bg-gradient-to-r {
    background: #f8fafc !important;
    color: #1f2937 !important;
  }
  
  .shadow-lg,
  .shadow-xl {
    box-shadow: none !important;
    border: 1px solid #e5e7eb !important;
  }
}
</style>
