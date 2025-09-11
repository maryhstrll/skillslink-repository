<template>
  <Layout @logout="handleLogout">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold text-base-content">Form Responses</h1>
          <p class="text-base-content/70 mt-1">
            View and manage all tracer form responses submitted by alumni
          </p>
        </div>
        <div class="flex gap-2">
          <button 
            class="btn btn-primary-custom shadow-lg hover:shadow-xl transition-all duration-200"
            @click="fetchForms"
            :disabled="loading"
          >
            <IconRefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
            Refresh
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Total Responses Card -->
        <StatCard
          title="Form Responses"
          :value="selectedForm ? (responseCounts[selectedForm.form_id] || 0) : 0"
          description="For selected form"
          :icon="IconFileCheck"
          variant="accent"
        />

        <!-- Remaining Alumni Card -->
        <StatCard
          title="Remaining Alumni"
          :value="remainingAlumni"
          description="Haven't responded yet"
          :icon="IconUsers"
          variant="secondary"
        />

        <!-- Response Rate Card -->
        <StatCard
          title="Response Rate"
          :value="responseRate || 0"
          description="of alumni"
          :icon="IconPercent"
          variant="ghost"
          format="percentage"
        />
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center my-12">
        <div class="text-center">
          <span class="loading loading-spinner loading-lg" style="color: var(--color-primary);"></span>
          <p class="mt-4 text-base-content/70">Loading form responses...</p>
        </div>
      </div>

      <!-- Form Selection and Responses -->
      <div v-else class="space-y-6">
        <!-- Form Selection Card -->
        <div class="card special-bg shadow-lg">
          <div class="card-body p-6">
            <h2 class="card-title text-xl mb-4 flex items-center gap-2">
              <IconSettings class="w-5 h-5" style="color: var(--color-primary);" />
              Form Selection
            </h2>
            
            <div class="form-control max-w-md">
              <label class="label">
                <span class="label-text font-medium">Select a form to view responses</span>
                <span class="label-text-alt">{{ forms.length }} forms available</span>
              </label>
              <div class="relative">
                <select 
                  class="select app-surface select-bordered w-full pr-10" 
                  v-model="selectedFormId"
                  @change="handleFormSelection"
                >
                  <option disabled value="0">Choose a form...</option>
                  <optgroup v-if="activeForms.length" label="📋 Active Forms">
                    <option 
                      v-for="form in activeForms" 
                      :key="form.form_id" 
                      :value="form.form_id"
                    >
                      {{ form.form_title }} ({{ form.form_year }}) - {{ responseCounts[form.form_id] || 0 }} responses
                    </option>
                  </optgroup>
                  <optgroup v-if="inactiveForms.length" label="📋 Inactive Forms">
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
                <IconChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50 pointer-events-none" />
              </div>
            </div>

            <!-- Selected Form Info -->
            <div v-if="selectedForm" class="mt-6 p-4 rounded-lg" style="background: var(--color-surface-alt);">
              <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <h3 class="font-bold text-lg text-base-content">{{ selectedForm.form_title }}</h3>
                    <span 
                      v-if="selectedForm.is_active" 
                      class="badge badge-accent"
                    >Active</span>
                    <span 
                      v-else 
                      class="badge badge-ghost"
                    >Inactive</span>
                  </div>
                  
                  <p class="text-base-content/70 mb-4">{{ selectedForm.form_description || 'No description available' }}</p>
                  
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="special-bg p-3 rounded-lg">
                      <div class="text-xs font-medium text-base-content/60 mb-1">YEAR</div>
                      <div class="flex items-center gap-2">
                        <IconCalendar class="h-4 w-4" style="color: var(--color-primary);" />
                        <span class="font-semibold">{{ selectedForm.form_year }}</span>
                      </div>
                    </div>
                    
                    <div class="bg-base-100 p-3 rounded-lg">
                      <div class="text-xs font-medium text-base-content/60 mb-1">RESPONSES</div>
                      <div class="flex items-center gap-2">
                        <IconUsers class="h-4 w-4" style="color: var(--color-accent);" />
                        <span class="font-semibold">{{ responseCounts[selectedForm.form_id] || 0 }}</span>
                      </div>
                    </div>
                    
                    <div class="bg-base-100 p-3 rounded-lg">
                      <div class="text-xs font-medium text-base-content/60 mb-1">QUESTIONS</div>
                      <div class="flex items-center gap-2">
                        <IconHelpCircle class="h-4 w-4" style="color: var(--color-ghost);" />
                        <span class="font-semibold">{{ getQuestionCount(selectedForm) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex justify-end">
                <button 
                  @click="viewAllResponses"
                  class="btn btn-primary-custom"
                  :disabled="!responseCounts[selectedForm.form_id]"
                >
                  <IconEye class="mr-2 h-4 w-4" />
                  View Detailed Responses
                </button>
              </div>
            </div>
            
            <!-- No Selection State -->
            <div v-if="!selectedForm" class="flex flex-col items-center justify-center py-12 text-center">
              <IconClipboardList class="h-16 w-16 opacity-25 mb-4" style="color: var(--color-primary);" />
              <h3 class="text-lg font-medium mb-2 text-base-content">No Form Selected</h3>
              <p class="text-base-content/70 max-w-sm">
                Please select a form from the dropdown above to view response statistics and details
              </p>
            </div>
          </div>
        </div>

        <!-- Responses DataTable -->
        <div v-if="selectedForm">
          <DataTable
            :title="`${selectedForm.form_title} - Responses`"
            :title-icon="IconFileText"
            :data="filteredResponses"
            :columns="responseColumns"
            :loading="loadingResponses"
            item-label="responses"
            empty-title="No responses found"
            :empty-description="formResponses.length === 0 ? 'No responses have been submitted for this form yet' : 'Try adjusting your search to see more results'"
            :empty-icon="IconInbox"
            key-field="response_id"
            loading-text="Loading responses..."
          >
            <!-- Custom cell for alumni name -->
            <template #cell-alumni_name="{ value, item }">
              <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                  <div class="bg-neutral text-neutral-content rounded-full w-8 h-8 flex items-center justify-center">
                    <span class="text-xs font-medium">{{ getInitials(value) }}</span>
                  </div>
                </div>
                <div>
                  <div class="font-medium">{{ value }}</div>
                  <div class="text-sm opacity-60">{{ item.student_id }}</div>
                </div>
              </div>
            </template>

            <!-- Custom cell for submission date -->
            <template #cell-submitted_at="{ value }">
              <span class="text-sm">
                {{ formatDate(value) }}
              </span>
            </template>

            <!-- Custom cell for completion status -->
            <template #cell-is_complete="{ value }">
              <div class="flex items-center gap-2">
                <span class="badge" :class="value ? 'badge-success' : 'badge-warning'">
                  {{ value ? 'Complete' : 'Incomplete' }}
                </span>
              </div>
            </template>

            <!-- Custom cell for actions -->
            <template #cell-actions="{ item }">
              <div class="flex gap-2">
                <button 
                  class="btn btn-sm btn-primary-custom"
                  @click="viewResponseDetails(item)"
                  title="View Details"
                >
                  <IconEye class="w-4 h-4" />
                </button>
              </div>
            </template>
          </DataTable>
        </div>
      </div>
    </div>

    <!-- Form Responses Component (Modal) -->
    <FormResponses ref="formResponsesComponent" />
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import FormResponses from '@/components/forms/FormResponses.vue'
import Layout from '@/components/layout/Layout.vue'
import DataTable from '@/components/tables/DataTable.vue'
import StatCard from '@/components/cards/StatCard.vue'
import { 
  ClipboardList as IconClipboardList,
  FileCheck as IconFileCheck,
  Percent as IconPercent,
  RefreshCw as IconRefreshCw,
  Settings as IconSettings,
  ChevronDown as IconChevronDown,
  Calendar as IconCalendar,
  Users as IconUsers,
  HelpCircle as IconHelpCircle,
  Eye as IconEye,
  FileText as IconFileText,
  Inbox as IconInbox
} from 'lucide-vue-next'

const router = useRouter()

// Configure axios
axios.defaults.baseURL = "http://localhost/skillslink/backend"
axios.defaults.withCredentials = true

// State
const loading = ref(true)
const forms = ref([])
const responseCounts = ref({})
const totalAlumni = ref(0)
const selectedForm = ref(null)
const selectedFormId = ref(0) // 0 means no selection
const formResponsesComponent = ref(null)

// Responses data
const formResponses = ref([])
const loadingResponses = ref(false)

// Search state
const searchQuery = ref('')

// Table columns configuration
const responseColumns = computed(() => [
  {
    key: 'alumni_name',
    title: 'Alumni',
    cellClass: 'font-medium'
  },
  {
    key: 'program_name',
    title: 'Program',
    cellClass: 'opacity-80'
  },
  {
    key: 'submitted_at',
    title: 'Submitted',
    cellClass: 'opacity-80'
  },
  {
    key: 'is_complete',
    title: 'Status'
  },
  {
    key: 'actions',
    title: 'Actions',
    cellClass: 'w-24'
  }
])

// Computed properties
const filteredResponses = computed(() => {
  if (!searchQuery.value.trim()) {
    return formResponses.value
  }
  
  const query = searchQuery.value.toLowerCase()
  return formResponses.value.filter(response => 
    (response.alumni_name && response.alumni_name.toLowerCase().includes(query)) ||
    (response.student_id && response.student_id.toLowerCase().includes(query)) ||
    (response.program_name && response.program_name.toLowerCase().includes(query))
  )
})

const totalResponses = computed(() => {
  return Object.values(responseCounts.value).reduce((sum, count) => sum + count, 0)
})

const remainingAlumni = computed(() => {
  console.log('Computing remainingAlumni:', {
    totalAlumni: totalAlumni.value,
    selectedForm: selectedForm.value?.form_id,
    currentFormResponses: selectedForm.value ? (responseCounts.value[selectedForm.value.form_id] || 0) : 0
  })
  
  if (!totalAlumni.value) return 0
  
  // If no form is selected, show total alumni
  if (!selectedForm.value) return totalAlumni.value
  
  // Get responses for the currently selected form only
  const currentFormResponses = responseCounts.value[selectedForm.value.form_id] || 0
  const remaining = Math.max(0, totalAlumni.value - currentFormResponses)
  
  console.log(`Remaining alumni: ${totalAlumni.value} - ${currentFormResponses} = ${remaining}`)
  return remaining
})

const responseRate = computed(() => {
  if (!totalAlumni.value || !selectedForm.value) return 0
  
  // Calculate response rate for the currently selected form
  const currentFormResponses = responseCounts.value[selectedForm.value.form_id] || 0
  return Math.round((currentFormResponses / totalAlumni.value) * 100)
})

const activeForms = computed(() => {
  return forms.value.filter(form => form.is_active)
})

const inactiveForms = computed(() => {
  return forms.value.filter(form => !form.is_active)
})

// Fetch forms and response counts
const fetchForms = async () => {
  try {
    loading.value = true
    const res = await axios.get('/admin/tracer_forms.php?action=list')
    forms.value = Array.isArray(res.data) ? res.data : []
    
    // Only fetch counts if we have forms
    if (forms.value.length > 0) {
      await fetchResponseCounts()
    } else {
      responseCounts.value = {}
    }
    
    await fetchAlumniCount()
    return forms.value
  } catch (err) {
    console.error('Error fetching forms:', err)
    forms.value = []
    responseCounts.value = {}
    return []
  } finally {
    loading.value = false
  }
}

const fetchResponseCounts = async () => {
  try {
    // Get all form IDs
    const formIds = forms.value.map(form => form.form_id)
    
    // Fetch counts for each form
    const counts = {}
    for (const formId of formIds) {
      try {
        const res = await axios.get(`/admin/form_responses.php?action=count&form_id=${formId}`)
        if (res.data && typeof res.data.count === 'number') {
          counts[formId] = res.data.count
        } else if (res.data && res.data.success === false) {
          console.warn(`Count endpoint returned error for form ${formId}:`, res.data)
          counts[formId] = 0
        } else {
          counts[formId] = 0
        }
      } catch (error) {
        console.error(`Error fetching count for form ${formId}:`, error)
        counts[formId] = 0
      }
    }
    responseCounts.value = counts
  } catch (err) {
    console.error('Error in fetchResponseCounts:', err)
    responseCounts.value = {}
  }
}

const fetchAlumniCount = async () => {
  try {
    // Use the alumni service to get all alumni, then count them
    const response = await fetch('http://localhost/skillslink/backend/admin/alumni.php', {
      credentials: 'include'
    })
    const data = await response.json()
    
    console.log('Alumni API response:', data)
    
    if (data.success && Array.isArray(data.data)) {
      totalAlumni.value = data.data.length
      console.log('Alumni count set to:', totalAlumni.value)
    } else {
      console.error('Invalid alumni API response:', data)
      totalAlumni.value = 0
    }
  } catch (err) {
    console.error('Error fetching alumni count:', err)
    console.log('Setting alumni count to 0 due to error')
    totalAlumni.value = 0
  }
}

const fetchFormResponses = async (formId) => {
  if (!formId) return
  
  try {
    loadingResponses.value = true
    formResponses.value = []
    
    const res = await axios.get(`/admin/form_responses.php?action=list&form_id=${formId}`)
    
    // Check if we got an array of responses
    if (Array.isArray(res.data)) {
      formResponses.value = res.data
      
      // Update the response count for this form to match actual data
      responseCounts.value[formId] = res.data.length
      
      console.log(`Loaded ${res.data.length} responses for form ID ${formId}`)
    } else {
      console.error('Unexpected response format:', res.data)
      formResponses.value = []
    }
  } catch (err) {
    console.error('Error fetching form responses:', err)
    
    // Check if it's an Axios error with a response
    if (err.response) {
      console.log(`Server returned status ${err.response.status} for form ID ${formId}`)
      
      // If server returned data, log it for debugging
      if (err.response.data) {
        console.log('Server error data:', err.response.data)
      }
    } else if (err.request) {
      // Request was made but no response received
      console.log('No response received from server')
    }
    
    formResponses.value = []
  } finally {
    loadingResponses.value = false
  }
}

// Format date for display
const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A'
  
  try {
    const date = new Date(dateStr)
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (e) {
    return dateStr
  }
}

// Get question count for a form
const getQuestionCount = (form) => {
  if (!form) return 0
  
  let count = 0
  
  // Count custom questions
  if (form.questions) {
    try {
      if (typeof form.questions === 'string') {
        const parsed = JSON.parse(form.questions)
        count += Array.isArray(parsed) ? parsed.length : 0
      } else if (Array.isArray(form.questions)) {
        count += form.questions.length
      }
    } catch (e) {
      console.error('Error parsing questions:', e)
    }
  }
  
  // Count employment questions
  if (form.selected_employment_questions) {
    try {
      if (typeof form.selected_employment_questions === 'string') {
        const parsed = JSON.parse(form.selected_employment_questions)
        count += Array.isArray(parsed) ? parsed.length : 0
      } else if (Array.isArray(form.selected_employment_questions)) {
        count += form.selected_employment_questions.length
      }
    } catch (e) {
      console.error('Error parsing employment questions:', e)
    }
  }
  
  return count
}

// Get initials for avatar
const getInitials = (name) => {
  if (!name) return 'N/A'
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

// Methods
const handleFormSelection = async () => {
  if (selectedFormId.value) {
    const form = forms.value.find(f => f.form_id === selectedFormId.value)
    if (form) {
      await selectForm(form)
    }
  } else {
    selectedForm.value = null
  }
}

const selectForm = async (form) => {
  selectedForm.value = form
  selectedFormId.value = form.form_id
  await fetchFormResponses(form.form_id)
}

const viewAllResponses = () => {
  if (selectedForm.value && formResponsesComponent.value) {
    formResponsesComponent.value.viewResponses(selectedForm.value)
  }
}

const viewResponseDetails = (response) => {
  // For now, just open the responses modal
  if (formResponsesComponent.value) {
    formResponsesComponent.value.viewResponses(selectedForm.value)
  }
}

const handleLogout = () => {
  router.push("/home")
}

// Fetch data on mount
onMounted(() => {
  fetchForms().then(() => {
    // Check for form_id in query params if available
    if (router && router.currentRoute && router.currentRoute.value && router.currentRoute.value.query) {
      const formId = parseInt(router.currentRoute.value.query.form_id)
      if (formId) {
        const matchingForm = forms.value.find(f => f.form_id === formId)
        if (matchingForm) {
          selectForm(matchingForm)
          return
        }
      }
    }
    
    // If no form_id in query or it wasn't valid, select the active form by default
    const activeForm = forms.value.find(f => f.is_active)
    if (activeForm) {
      selectForm(activeForm)
    } else if (forms.value.length > 0) {
      // If no active form found, select the first form in the list
      console.log('No active form found, selecting first form')
      selectForm(forms.value[0])
    } else {
      console.warn('No forms available to select')
    }
  })
})
</script>

<style scoped>
/* Custom styling to match design system */
h1, p {
  color: var(--color-text-primary);
}

/* Card hover effects */
.card:hover {
  transform: translateY(-2px);
}

/* Loading states */
.loading {
  color: var(--color-primary);
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

/* Animation */
.btn, .card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
