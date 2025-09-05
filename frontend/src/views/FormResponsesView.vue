<template>
    <Layout @logout="handleLogout">
    <div class="container mx-auto px-4 py-6">
      <!-- Page Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary mb-2">Form Responses</h1>
        <p class="text-sm opacity-75">
          View and manage all tracer form responses submitted by alumni
        </p>
      </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <!-- Total Forms Card -->
      <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="card-title text-base mb-1">Total Forms</h3>
              <p class="text-3xl font-bold">{{ forms.length }}</p>
            </div>
            <div class="p-3 rounded-full bg-primary/10">
              <IconClipboardList class="w-6 h-6 text-primary" />
            </div>
          </div>
        </div>
      </div>

      <!-- Total Responses Card -->
      <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="card-title text-base mb-1">Total Responses</h3>
              <p class="text-3xl font-bold">{{ totalResponses || 0 }}</p>
            </div>
            <div class="p-3 rounded-full bg-success/10">
              <IconFileCheck class="w-6 h-6 text-success" />
            </div>
          </div>
        </div>
      </div>

      <!-- Response Rate Card -->
      <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="card-title text-base mb-1">Response Rate</h3>
              <div class="flex items-end gap-2">
                <p class="text-3xl font-bold">{{ responseRate || 0 }}%</p>
                <p class="text-sm opacity-75 mb-1">of alumni</p>
              </div>
            </div>
            <div class="p-3 rounded-full bg-info/10">
              <IconPercentCircle class="w-6 h-6 text-info" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center my-8">
      <span class="loading loading-spinner loading-lg text-primary"></span>
    </div>

    <!-- Response Analysis -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-5 gap-6">
      <!-- Forms Selector Column -->
      <div class="lg:col-span-3">
        <div class="card bg-base-100 shadow">
          <div class="card-body p-4">
            <div class="flex flex-col mb-6">
              <h2 class="card-title text-lg mb-4">Select Form to View Responses</h2>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Employment Tracer Form</span>
                  <span class="label-text-alt">{{ forms.length }} forms available</span>
                </label>
                <div class="relative">
                  <select 
                    class="select select-bordered w-full pr-16" 
                    v-model="selectedFormId"
                    @change="handleFormSelection"
                  >
                    <option disabled value="0">Select a form...</option>
                    <optgroup v-if="activeForms.length" label="Active Forms">
                      <option 
                        v-for="form in activeForms" 
                        :key="form.form_id" 
                        :value="form.form_id"
                      >
                        {{ form.form_title }} ({{ form.form_year }}) - {{ responseCounts[form.form_id] || 0 }} responses
                      </option>
                    </optgroup>
                    <optgroup v-if="inactiveForms.length" label="Inactive Forms">
                      <option 
                        v-for="form in inactiveForms" 
                        :key="form.form_id" 
                        :value="form.form_id"
                      >
                        {{ form.form_title }} ({{ form.form_year }}) - {{ responseCounts[form.form_id] || 0 }} responses
                      </option>
                    </optgroup>
                    <option v-if="!forms.length" disabled>No forms available</option>
                  </select>
                  <label class="absolute right-4 top-1/2 -translate-y-1/2">
                    <IconChevronDown class="h-5 w-5 opacity-50" />
                  </label>
                </div>
              </div>
            </div>

            <!-- Selected Form Info -->
            <div v-if="selectedForm" class="bg-base-200 p-4 rounded-lg mb-4">
              <div class="flex items-start justify-between">
                <div>
                  <div class="flex items-center gap-2">
                    <h3 class="font-bold mb-1">{{ selectedForm.form_title }}</h3>
                    <span 
                      v-if="selectedForm.is_active" 
                      class="badge badge-success badge-sm"
                    >Active</span>
                    <span 
                      v-else 
                      class="badge badge-ghost badge-sm"
                    >Inactive</span>
                  </div>
                  
                  <p class="text-sm opacity-75 mb-3">{{ selectedForm.form_description || 'No description available' }}</p>
                  
                  <div class="grid grid-cols-2 gap-3 mt-3">
                    <div class="bg-base-100 p-2 rounded">
                      <div class="text-xs opacity-75">Year</div>
                      <div class="flex items-center gap-1">
                        <IconCalendar class="h-4 w-4" />
                        <span>{{ selectedForm.form_year }}</span>
                      </div>
                    </div>
                    
                    <div class="bg-base-100 p-2 rounded">
                      <div class="text-xs opacity-75">Responses</div>
                      <div class="flex items-center gap-1">
                        <IconUsers class="h-4 w-4" />
                        <span>{{ responseCounts[selectedForm.form_id] || 0 }}</span>
                      </div>
                    </div>
                    
                    <div class="bg-base-100 p-2 rounded col-span-2">
                      <div class="text-xs opacity-75">Form Questions</div>
                      <div class="flex items-center gap-1">
                        <IconClipboardList class="h-4 w-4" />
                        <span>{{ getQuestionCount(selectedForm) || 0 }} questions</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div v-if="selectedForm" class="flex justify-end gap-2">
              <button 
                @click="viewAllResponses"
                class="btn btn-primary"
                :disabled="!responseCounts[selectedForm.form_id]"
              >
                <IconEye class="mr-2 h-4 w-4" />
                View All Details
              </button>
            </div>
            
            <!-- No Selection -->
            <div v-if="!selectedForm" class="flex flex-col items-center justify-center py-10 text-center">
              <IconClipboardList class="h-16 w-16 opacity-25 mb-4" />
              <h3 class="text-lg font-medium mb-2">No Form Selected</h3>
              <p class="text-sm opacity-75 max-w-xs">
                Please select a form from the dropdown to view response statistics
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Responses Column -->
      <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow">
          <div class="card-body p-4">
            <h2 class="card-title text-lg mb-4">Form Responses</h2>
            
            <div v-if="selectedForm">
              <div class="text-center mb-6">
                <h3 class="font-bold">{{ selectedForm.form_title }}</h3>
                <p class="text-sm opacity-75">{{ selectedForm.form_year }}</p>
                <div class="badge badge-primary mt-2">{{ responseCounts[selectedForm.form_id] || 0 }} responses</div>
              </div>

              <div v-if="formResponses.length > 0" class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                <div v-for="(response, index) in formResponses" :key="index" class="bg-base-200 p-3 rounded-lg">
                  <div class="flex justify-between items-start mb-2">
                    <div>
                      <p class="font-medium">{{ response.alumni_name || 'Anonymous' }}</p>
                      <p class="text-xs opacity-70">{{ response.alumni_email || 'No email' }}</p>
                    </div>
                    <span class="badge badge-sm" :class="response.has_form_data ? 'badge-success' : 'badge-warning'">
                      {{ response.has_form_data ? 'Complete' : 'Incomplete' }}
                    </span>
                  </div>
                  <div class="text-xs mt-1 opacity-70">Submitted: {{ formatDate(response.submitted_at) }}</div>
                </div>
              </div>
              
              <div v-else-if="loadingResponses" class="flex justify-center items-center py-10">
                <span class="loading loading-spinner loading-md"></span>
              </div>
              
              <div v-else class="flex flex-col items-center justify-center py-10 text-center">
                <IconInbox class="h-10 w-10 opacity-25 mb-3" />
                <p class="text-sm opacity-75">No responses for this form yet</p>
              </div>

              <button 
                @click="viewAllResponses"
                class="btn btn-primary w-full mt-4"
                :disabled="!responseCounts[selectedForm.form_id]"
              >
                View All Details
              </button>
            </div>
            
            <div v-else class="flex flex-col items-center justify-center py-10 text-center">
              <IconClipboardCheck class="h-16 w-16 opacity-25 mb-4" />
              <h3 class="text-lg font-medium mb-2">No Form Selected</h3>
              <p class="text-sm opacity-75 max-w-xs">
                Select a form from the dropdown to view responses
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Form Responses Component (Modal) -->
  <FormResponses ref="formResponsesComponent" />
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import FormResponses from '@/components/FormResponses.vue';
import Layout from '@/components/Layout.vue';
import { Cpu } from 'lucide-vue-next';

// Configure axios
axios.defaults.baseURL = "http://localhost/skillslink/backend";
axios.defaults.withCredentials = true;

// State
const loading = ref(true);
const forms = ref([]);
const responseCounts = ref({});
const totalAlumni = ref(0);
const selectedForm = ref(null);
const selectedFormId = ref(0); // 0 means no selection
const formResponsesComponent = ref(null);

// Responses data
const formResponses = ref([]);
const loadingResponses = ref(false);

// Fetch forms and response counts
const fetchForms = async () => {
  try {
    loading.value = true;
    const res = await axios.get('/admin/tracer_forms.php?action=list');
    forms.value = Array.isArray(res.data) ? res.data : [];
    
    // Only fetch counts if we have forms
    if (forms.value.length > 0) {
      await fetchResponseCounts();
    } else {
      responseCounts.value = {};
    }
    
    await fetchAlumniCount();
    return forms.value;
  } catch (err) {
    console.error('Error fetching forms:', err);
    forms.value = [];
    responseCounts.value = {};
    return [];
  } finally {
    loading.value = false;
  }
};

const fetchResponseCounts = async () => {
  try {
    // Get all form IDs
    const formIds = forms.value.map(form => form.form_id);
    
    // Fetch counts for each form
    const counts = {};
    for (const formId of formIds) {
      try {
        const res = await axios.get(`/admin/form_responses.php?action=count&form_id=${formId}`);
        if (res.data && typeof res.data.count === 'number') {
          counts[formId] = res.data.count;
        } else if (res.data && res.data.success === false) {
          console.warn(`Count endpoint returned error for form ${formId}:`, res.data);
          counts[formId] = 0;
        } else {
          counts[formId] = 0;
        }
      } catch (error) {
        console.error(`Error fetching count for form ${formId}:`, error);
        counts[formId] = 0;
      }
    }
    responseCounts.value = counts;
  } catch (err) {
    console.error('Error in fetchResponseCounts:', err);
    responseCounts.value = {};
  }
};

const fetchAlumniCount = async () => {
  try {
    const res = await axios.get('/api/users.php?action=count&role=alumni');
    totalAlumni.value = res.data?.count || 0;
    
    // If we couldn't get the count, set a fallback value
    if (totalAlumni.value === 0) {
      console.warn('Alumni count is 0, using fallback value');
      totalAlumni.value = 100; // Fallback value for demonstration purposes
    }
  } catch (err) {
    console.error('Error fetching alumni count:', err);
    totalAlumni.value = 100; // Fallback value on error
  }
};

const fetchFormResponses = async (formId) => {
  if (!formId) return;
  
  try {
    loadingResponses.value = true;
    formResponses.value = [];
    
    const res = await axios.get(`/admin/form_responses.php?action=list&form_id=${formId}`);
    
    // Check if we got an array of responses
    if (Array.isArray(res.data)) {
      formResponses.value = res.data;
      
      // Update the response count for this form to match actual data
      responseCounts.value[formId] = res.data.length;
      
      console.log(`Loaded ${res.data.length} responses for form ID ${formId}`);
    } else {
      console.error('Unexpected response format:', res.data);
      formResponses.value = [];
    }
  } catch (err) {
    console.error('Error fetching form responses:', err);
    
    // Check if it's an Axios error with a response
    if (err.response) {
      console.log(`Server returned status ${err.response.status} for form ID ${formId}`);
      
      // If server returned data, log it for debugging
      if (err.response.data) {
        console.log('Server error data:', err.response.data);
      }
    } else if (err.request) {
      // Request was made but no response received
      console.log('No response received from server');
    }
    
    formResponses.value = [];
  } finally {
    loadingResponses.value = false;
  }
};

// Format date for display
const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  
  try {
    const date = new Date(dateStr);
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch (e) {
    return dateStr;
  }
};

// Get question count for a form
const getQuestionCount = (form) => {
  if (!form) return 0;
  
  let count = 0;
  
  // Count custom questions
  if (form.questions) {
    try {
      if (typeof form.questions === 'string') {
        const parsed = JSON.parse(form.questions);
        count += Array.isArray(parsed) ? parsed.length : 0;
      } else if (Array.isArray(form.questions)) {
        count += form.questions.length;
      }
    } catch (e) {
      console.error('Error parsing questions:', e);
    }
  }
  
  // Count employment questions
  if (form.selected_employment_questions) {
    try {
      if (typeof form.selected_employment_questions === 'string') {
        const parsed = JSON.parse(form.selected_employment_questions);
        count += Array.isArray(parsed) ? parsed.length : 0;
      } else if (Array.isArray(form.selected_employment_questions)) {
        count += form.selected_employment_questions.length;
      }
    } catch (e) {
      console.error('Error parsing employment questions:', e);
    }
  }
  
  return count;
};

// Computed properties
const totalResponses = computed(() => {
  return Object.values(responseCounts.value).reduce((sum, count) => sum + count, 0);
});

const responseRate = computed(() => {
  if (!totalAlumni.value) return 0;
  return Math.round((totalResponses.value / totalAlumni.value) * 100);
});

const activeForms = computed(() => {
  return forms.value.filter(form => form.is_active);
});

const inactiveForms = computed(() => {
  return forms.value.filter(form => !form.is_active);
});

// Methods
const handleFormSelection = async () => {
  if (selectedFormId.value) {
    const form = forms.value.find(f => f.form_id === selectedFormId.value);
    if (form) {
      await selectForm(form);
    }
  } else {
    selectedForm.value = null;
  }
};

const selectForm = async (form) => {
  selectedForm.value = form;
  selectedFormId.value = form.form_id;
  await fetchFormResponses(form.form_id);
};

const viewAllResponses = () => {
  if (selectedForm.value && formResponsesComponent.value) {
    formResponsesComponent.value.viewResponses(selectedForm.value);
  }
};

// Fetch data on mount
onMounted(() => {
  // Get router instance
  const router = useRouter();
  
  fetchForms().then(() => {
    // Check for form_id in query params if available
    if (router && router.currentRoute && router.currentRoute.value && router.currentRoute.value.query) {
      const formId = parseInt(router.currentRoute.value.query.form_id);
      if (formId) {
        const matchingForm = forms.value.find(f => f.form_id === formId);
        if (matchingForm) {
          selectForm(matchingForm);
          return;
        }
      }
    }
    
    // If no form_id in query or it wasn't valid, select the active form by default
    const activeForm = forms.value.find(f => f.is_active);
    if (activeForm) {
      selectForm(activeForm);
    } else if (forms.value.length > 0) {
      // If no active form found, select the first form in the list
      console.log('No active form found, selecting first form');
      selectForm(forms.value[0]);
    } else {
      console.warn('No forms available to select');
    }
  });
});

const handleLogout = () => {
    router.push("/home")
}
</script>
