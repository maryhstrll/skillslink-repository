<template>
  <Layout @logout="handleLogout">
    <div class="p-6 max-w-4xl mx-auto">
      <h1 class="text-3xl font-bold mb-6 text-primary">Alumni Employment Tracer Form</h1>

      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="loading loading-spinner loading-lg"></div>
        <span class="ml-2">Loading form...</span>
      </div>

      <div v-else>
        <!-- Form Description -->
        <div v-if="form_description" class="mb-6 p-4 bg-base-200 rounded-lg">
          <p class="text-gray-700">{{ form_description }}</p>
          <div class="text-sm text-gray-500 mt-2">Form Year: {{ form_year }}</div>
        </div>

        <!-- Already Responded Message -->
        <div v-if="alreadyResponded && !editMode" class="space-y-4 mb-6">
          <div class="alert alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ alreadyMessage }}</span>
          </div>
          
          <div class="flex gap-4">
            <button 
              @click="showExistingResponses = !showExistingResponses"
              class="btn btn-outline"
            >
              {{ showExistingResponses ? 'Hide' : 'View' }} My Responses
            </button>
            
            <button 
              @click="enableEditing"
              class="btn btn-primary"
            >
              Edit My Responses
            </button>
          </div>
        </div>

        <!-- Show existing responses (view mode) -->
        <div v-if="showExistingResponses && !editMode" class="space-y-6 mb-6">
          <!-- Employment Data -->
          <div class="card bg-base-100 shadow-md">
            <div class="card-body">
              <h3 class="card-title text-lg mb-4">Your Employment Information:</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="question in coreQuestions" :key="question.id" 
                     v-show="existingEmploymentData[question.maps_to]" class="p-3 bg-gray-50 rounded">
                  <div class="font-medium text-sm text-black">{{ question.label }}:</div>
                  <div class="text-gray-700">{{ formatValue(existingEmploymentData[question.maps_to]) }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Additional Responses -->
          <div v-if="Object.keys(existingAdditionalData).length" class="card bg-base-100 shadow-md">
            <div class="card-body">
              <h3 class="card-title text-lg mb-4">Additional Survey Responses:</h3>
              <div class="space-y-3">
                <div v-for="(answer, questionId) in existingAdditionalData" :key="questionId" 
                     class="p-3 bg-gray-50 rounded">
                  <div class="font-medium text-sm">{{ getQuestionLabel(questionId) }}:</div>
                  <div class="text-gray-700">{{ Array.isArray(answer) ? answer.join(", ") : answer }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form to fill out or edit -->
        <div v-if="!alreadyResponded || editMode">
          <form @submit.prevent="submitForm" class="space-y-6">
            <!-- Core Employment Section -->
            <div class="card bg-base-100 shadow-md">
              <div class="card-body">
                <h2 class="card-title text-xl mb-4 flex items-center">
                  <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 002 2h4a2 2 0 002-2V4"></path>
                  </svg>
                  Employment Information
                </h2>
                
                <div class="space-y-6">
                  <div v-for="question in coreQuestions" :key="question.id" 
                       v-show="shouldShowQuestion(question)" 
                       class="form-control">
                    <label class="label">
                      <span class="label-text font-medium">
                        {{ question.label }}
                        <span v-if="question.required" class="text-red-500">*</span>
                      </span>
                    </label>

                    <!-- Radio buttons -->
                    <div v-if="question.type === 'radio'" class="space-y-2">
                      <label v-for="option in question.options" :key="option" 
                             class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" 
                               :name="question.id"
                               :value="option.toLowerCase().replace(/ /g, '_')"
                               v-model="employmentData[question.maps_to]"
                               class="radio radio-primary" />
                        <span>{{ option }}</span>
                      </label>
                    </div>

                    <!-- Text inputs -->
                    <input v-else-if="question.type === 'text'"
                           type="text"
                           v-model="employmentData[question.maps_to]"
                           :placeholder="question.placeholder || 'Enter ' + question.label.toLowerCase()"
                           class="input input-bordered w-full" />

                    <!-- Textarea -->
                    <textarea v-else-if="question.type === 'textarea'"
                              v-model="employmentData[question.maps_to]"
                              :placeholder="question.placeholder || 'Enter ' + question.label.toLowerCase()"
                              class="textarea textarea-bordered w-full"
                              rows="3"></textarea>

                    <!-- Number inputs -->
                    <input v-else-if="question.type === 'number'"
                           type="number"
                           v-model="employmentData[question.maps_to]"
                           :min="question.min"
                           :max="question.max"
                           class="input input-bordered w-full" />

                    <!-- Select dropdowns -->
                    <select v-else-if="question.type === 'select'"
                            v-model="employmentData[question.maps_to]"
                            class="select select-bordered w-full">
                      <option disabled value="">Select an option</option>
                      <option v-for="option in question.options" :key="option" 
                              :value="option.toLowerCase().replace(/[^a-z0-9]/g, '_')">
                        {{ option }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Additional Questions Section -->
            <div v-if="additionalQuestions.length" class="card bg-base-100 shadow-md">
              <div class="card-body">
                <h2 class="card-title text-xl mb-4 flex items-center">
                  <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                  </svg>
                  Additional Survey Questions
                </h2>
                
                <div class="space-y-6">
                  <div v-for="question in additionalQuestions" :key="question.id" class="form-control">
                    <label class="label">
                      <span class="label-text font-medium">{{ question.label }}</span>
                    </label>

                    <input v-if="question.type === 'text'"
                           type="text"
                           v-model="additionalData[question.id]"
                           :placeholder="question.placeholder"
                           class="input input-bordered w-full" />

                    <textarea v-else-if="question.type === 'textarea'"
                              v-model="additionalData[question.id]"
                              :placeholder="question.placeholder"
                              class="textarea textarea-bordered w-full"
                              rows="3"></textarea>

                    <input v-else-if="question.type === 'number'"
                           type="number"
                           v-model="additionalData[question.id]"
                           :min="question.min"
                           :max="question.max"
                           class="input input-bordered w-full" />

                    <div v-else-if="question.type === 'radio'" class="space-y-2">
                      <label v-for="option in question.options" :key="option"
                             class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="radio" 
                               :name="question.id"
                               :value="option"
                               v-model="additionalData[question.id]"
                               class="radio radio-primary" />
                        <span>{{ option }}</span>
                      </label>
                    </div>

                    <div v-else-if="question.type === 'checkbox'" class="space-y-2">
                      <label v-for="option in question.options" :key="option"
                             class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                        <input type="checkbox" 
                               :value="option"
                               @change="handleCheckboxChange(question.id, option, $event)"
                               :checked="additionalData[question.id] && additionalData[question.id].includes(option)"
                               class="checkbox checkbox-primary" />
                        <span>{{ option }}</span>
                      </label>
                    </div>

                    <select v-else-if="question.type === 'select'"
                            v-model="additionalData[question.id]"
                            class="select select-bordered w-full">
                      <option disabled value="">Select an option</option>
                      <option v-for="option in question.options" :key="option" :value="option">
                        {{ option }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-4">
              <button v-if="editMode" type="button" @click="cancelEditing" class="btn btn-outline">
                Cancel Editing
              </button>
              <button type="submit" class="btn btn-primary btn-lg" :disabled="!isFormValid() || submitting">
                <span v-if="submitting" class="loading loading-spinner loading-sm"></span>
                {{ submitting ? 'Submitting...' : (editMode ? 'Update Employment Tracer' : 'Submit Employment Tracer') }}
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
import Layout from "@/components/Layout.vue";
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
const editMode = ref(false);

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

const isFormValid = () => {
  // Check required core questions
  return employmentData.employment_status !== '';
};

const formatValue = (value) => {
  if (!value) return 'Not provided';
  
  // Convert underscores to spaces and capitalize
  return value.toString().replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
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

const enableEditing = () => {
  editMode.value = true;
  
  // Populate form data with existing data
  Object.assign(employmentData, existingEmploymentData.value);
  Object.assign(additionalData, existingAdditionalData.value);
  
  // Ensure checkbox answers are arrays
  if (Array.isArray(additionalQuestions.value)) {
    additionalQuestions.value.forEach(q => {
      if (q.type === 'checkbox' && !Array.isArray(additionalData[q.id])) {
        additionalData[q.id] = additionalData[q.id] ? [additionalData[q.id]] : [];
      }
    });
  }
};

const cancelEditing = () => {
  editMode.value = false;
  
  // Reset form data
  Object.keys(employmentData).forEach(key => {
    employmentData[key] = '';
  });
  Object.keys(additionalData).forEach(key => {
    delete additionalData[key];
  });
  
  // Reinitialize additional data
  if (Array.isArray(additionalQuestions.value)) {
    additionalQuestions.value.forEach(q => {
      additionalData[q.id] = q.type === 'checkbox' ? [] : '';
    });
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
  if (!isFormValid()) {
    alert("Please fill in all required fields");
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
      const message = editMode.value ? "Employment tracer updated successfully!" : "Employment tracer submitted successfully!";
      alert(message);
      
      // Update state after successful submission
      alreadyResponded.value = true;
      alreadyMessage.value = `You have successfully submitted your employment tracer form for ${form_year.value}.`;
      existingEmploymentData.value = { ...employmentData };
      existingAdditionalData.value = { ...additionalData };
      editMode.value = false;
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
/* Demonstrating use of the CSS variable color system for this view */

.edit-btn {
  background: var(--color-accent);
  color: var(--color-text-invert);
  border: 1px solid rgb(var(--color-accent-rgb) / 0.4);
  transition: background .18s, box-shadow .18s, transform .12s;
}
.edit-btn:hover {
  background: var(--color-accent-hover);
  box-shadow: 0 4px 10px -2px rgb(var(--color-accent-rgb) / 0.45);
}
.edit-btn:active {
  transform: translateY(1px);
}

/* Re-style the already-responded message using semantic tokens */
.message {
  background: rgb(var(--color-light-blue-rgb) / 0.18);
  border: 1px solid rgb(var(--color-light-blue-rgb) / 0.45);
  color: var(--color-text);
}
.field {
  background: var(--color-surface);
}
</style>
