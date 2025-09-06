<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto relative transform transition-all duration-300 scale-100">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-[#2E79BA] to-[#1E549F] p-6 rounded-t-2xl z-10">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl md:text-2xl font-bold text-white">
              Alumni Profile
            </h3>
            <p class="text-white/80 mt-1">
              Detailed view of {{ alumni?.first_name }} {{ alumni?.last_name }}
            </p>
          </div>
          <button class="btn btn-sm btn-circle bg-white/20 border-none text-white hover:bg-white/30 backdrop-blur-sm" 
                  @click="closeModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center p-12">
        <div class="loading loading-spinner loading-lg text-[#2E79BA]"></div>
        <span class="ml-4 text-gray-600">Loading profile details...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="p-6">
        <div class="alert alert-error bg-red-500/20 border border-red-500/30 text-red-800 rounded-xl">
          <i class="fas fa-exclamation-triangle"></i>
          <span>{{ error }}</span>
        </div>
      </div>

      <!-- Profile Content -->
      <div v-else-if="profileData" class="p-6">
        <!-- Basic Info Card -->
        <div class="card bg-gradient-to-r from-[#5FC9F3]/10 to-[#2E79BA]/10 border border-[#5FC9F3]/20 mb-6">
          <div class="card-body">
            <div class="flex items-center gap-6">
              <!-- Profile Avatar -->
              <div class="avatar">
                <div class="w-20 rounded-full ring ring-[#5FC9F3] ring-offset-base-100 ring-offset-2">
                  <div class="w-full h-full bg-gradient-to-br from-[#2E79BA] to-[#1E549F] flex items-center justify-center text-white text-2xl font-bold">
                    {{ getInitials(profileData.first_name, profileData.last_name) }}
                  </div>
                </div>
              </div>
              
              <!-- Basic Details -->
              <div class="flex-1">
                <h2 class="text-2xl font-bold text-[#081F37] mb-2">
                  {{ profileData.first_name }} 
                  <span v-if="profileData.middle_name" class="text-[#2E79BA]">{{ profileData.middle_name }}</span>
                  {{ profileData.last_name }}
                </h2>
                <div class="flex flex-wrap gap-4 text-sm">
                  <div class="badge bg-[#5FC9F3] text-white">
                    ID: {{ profileData.student_id }}
                  </div>
                  <div class="badge bg-[#2E79BA] text-white">
                    {{ profileData.program_name || `Program ${profileData.program_id}` }}
                  </div>
                  <div class="badge bg-[#1E549F] text-white">
                    Graduated: {{ profileData.year_graduated }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Detailed Information Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Personal Information -->
          <div class="card bg-base-100 shadow-md border border-gray-200">
            <div class="card-body">
              <h3 class="card-title text-[#2E79BA] mb-4">
                <i class="fas fa-user mr-2"></i>Personal Information
              </h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600 font-medium">Full Name:</span>
                  <span class="text-[#5FC9F3] font-semibold">
                    {{ profileData.first_name }} {{ profileData.middle_name }} {{ profileData.last_name }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 font-medium">Student ID:</span>
                  <span class="text-[#2E79BA] font-semibold">{{ profileData.student_id }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 font-medium">Phone:</span>
                  <span class="text-[#1E549F] font-medium">{{ profileData.phone_number || 'Not provided' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Academic Information -->
          <div class="card bg-base-100 shadow-md border border-gray-200">
            <div class="card-body">
              <h3 class="card-title text-[#2E79BA] mb-4">
                <i class="fas fa-graduation-cap mr-2"></i>Academic Information
              </h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600 font-medium">Program:</span>
                  <span class="text-[#5FC9F3] font-semibold">
                    {{ profileData.program_name || `Program ${profileData.program_id}` }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 font-medium">Year Graduated:</span>
                  <span class="text-[#2E79BA] font-semibold">{{ profileData.year_graduated }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 font-medium">Program ID:</span>
                  <span class="text-[#1E549F] font-medium">{{ profileData.program_id }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Address Information -->
          <div class="card bg-base-100 shadow-md border border-gray-200">
            <div class="card-body">
              <h3 class="card-title text-[#2E79BA] mb-4">
                <i class="fas fa-map-marker-alt mr-2"></i>Address Information
              </h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600 font-medium">Barangay:</span>
                  <span class="text-[#5FC9F3] font-medium">{{ profileData.barangay || 'Not provided' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 font-medium">City:</span>
                  <span class="text-[#2E79BA] font-medium">{{ profileData.city || 'Not provided' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600 font-medium">Province:</span>
                  <span class="text-[#1E549F] font-medium">{{ profileData.province || 'Not provided' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Social Links -->
          <div class="card bg-base-100 shadow-md border border-gray-200">
            <div class="card-body">
              <h3 class="card-title text-[#2E79BA] mb-4">
                <i class="fas fa-share-alt mr-2"></i>Social Media Links
              </h3>
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-gray-600 font-medium">LinkedIn:</span>
                  <div v-if="profileData.linkedin_profile">
                    <a :href="profileData.linkedin_profile" 
                       target="_blank" 
                       class="btn btn-sm bg-[#0077B5] text-white border-none hover:bg-[#005885]">
                      <i class="fab fa-linkedin mr-1"></i>View Profile
                    </a>
                  </div>
                  <span v-else class="text-gray-400">Not provided</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600 font-medium">Facebook:</span>
                  <div v-if="profileData.facebook_profile">
                    <a :href="profileData.facebook_profile" 
                       target="_blank" 
                       class="btn btn-sm bg-[#1877F2] text-white border-none hover:bg-[#166FE5]">
                      <i class="fab fa-facebook mr-1"></i>View Profile
                    </a>
                  </div>
                  <span v-else class="text-gray-400">Not provided</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Employment Status (Real-time from employment_records) -->
          <div class="card bg-base-100 shadow-md border border-gray-200">
            <div class="card-body">
              <h3 class="card-title text-[#2E79BA] mb-4">
                <i class="fas fa-briefcase mr-2"></i>Employment Status
              </h3>
              <div v-if="loading">
                <div class="loading loading-spinner loading-sm text-[#2E79BA]"></div>
                <span class="ml-2 text-gray-500">Loading employment data...</span>
              </div>
              <div v-else-if="employmentData" class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600 font-medium">Status:</span>
                  <span class="text-[#5FC9F3] font-semibold">{{ formatEmploymentStatus(employmentData.employment_status) }}</span>
                </div>
                <div v-if="employmentData.company_name" class="flex justify-between">
                  <span class="text-gray-600 font-medium">Company:</span>
                  <span class="text-[#2E79BA] font-medium">{{ employmentData.company_name }}</span>
                </div>
                <div v-if="employmentData.job_title" class="flex justify-between">
                  <span class="text-gray-600 font-medium">Position:</span>
                  <span class="text-[#1E549F] font-medium">{{ employmentData.job_title }}</span>
                </div>
                <div v-if="employmentData.employment_type" class="flex justify-between">
                  <span class="text-gray-600 font-medium">Employment Type:</span>
                  <span class="text-[#2E79BA] font-medium">{{ formatEmploymentType(employmentData.employment_type) }}</span>
                </div>
                <div v-if="employmentData.salary_range" class="flex justify-between">
                  <span class="text-gray-600 font-medium">Salary Range:</span>
                  <span class="text-[#1E549F] font-medium">{{ employmentData.salary_range }}</span>
                </div>
                <div v-if="employmentData.date_employed" class="flex justify-between">
                  <span class="text-gray-600 font-medium">Date Employed:</span>
                  <span class="text-[#2E79BA] font-medium">{{ formatDate(employmentData.date_employed) }}</span>
                </div>
                <div v-if="employmentData.submitted_at" class="flex justify-between">
                  <span class="text-gray-600 font-medium">Last Updated:</span>
                  <span class="text-gray-500 text-sm">{{ formatDate(employmentData.submitted_at) }}</span>
                </div>
              </div>
              <div v-else class="text-gray-500">
                <p>No employment information available from tracer forms.</p>
                <p class="text-sm mt-2">Alumni needs to complete the employment tracer form to update this information.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="sticky bottom-0 bg-gray-50 p-4 rounded-b-2xl border-t">
        <div class="flex justify-end gap-3">
          <button class="btn bg-gray-200 text-gray-700 border-none hover:bg-gray-300" 
                  @click="closeModal">
            Close
          </button>
          <button class="btn bg-[#2E79BA] text-white border-none hover:bg-[#1E549F]" 
                  @click="editAlumni"
                  :disabled="loading">
            <i class="fas fa-edit mr-2"></i>Edit Alumni
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import alumniService from '@/services/alumni.js'

// Props
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  alumni: {
    type: Object,
    default: null
  }
})

// Emits
const emit = defineEmits(['close', 'edit'])

// State
const loading = ref(false)
const error = ref(null)
const profileData = ref(null)
const employmentData = ref(null)

// Methods
const closeModal = () => {
  emit('close')
}

const editAlumni = () => {
  emit('edit', props.alumni)
  closeModal()
}

const getInitials = (firstName, lastName) => {
  if (!firstName && !lastName) return '?'
  const firstInitial = firstName ? firstName.charAt(0).toUpperCase() : ''
  const lastInitial = lastName ? lastName.charAt(0).toUpperCase() : ''
  return firstInitial + lastInitial || '?'
}

const formatDate = (dateString) => {
  if (!dateString) return null;
  
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  } catch (error) {
    return dateString;
  }
};

const formatEmploymentStatus = (status) => {
  if (!status) return null;
  
  const statusMap = {
    'Employed': 'Employed',
    'Unemployed': 'Unemployed', 
    'Further Studies': 'Further Studies',
    'Not Looking': 'Not Looking for Work',
    'employed': 'Employed',
    'unemployed': 'Unemployed',
    'self_employed': 'Self Employed',
    'further_studies': 'Further Studies',
    'not_looking': 'Not Looking for Work'
  };
  
  return statusMap[status] || status;
};

const formatEmploymentType = (type) => {
  if (!type) return null;
  
  const typeMap = {
    'Permanent': 'Permanent',
    'Casual': 'Casual',
    'Contractual': 'Contractual',
    'Seasonal or Short Term': 'Seasonal or Short Term',
    'full_time': 'Full Time',
    'part_time': 'Part Time',
    'contract': 'Contract',
    'freelance': 'Freelance',
    'internship': 'Internship'
  };
  
  return typeMap[type] || type;
};

const fetchEmploymentData = async (alumniId) => {
  if (!alumniId) return;
  
  try {
    const response = await alumniService.getAlumniEmployment(alumniId);
    
    if (response.success && response.employment) {
      employmentData.value = response.employment;
    } else {
      employmentData.value = null;
    }
  } catch (err) {
    console.error('Error fetching employment data:', err);
    employmentData.value = null;
  }
};

const loadProfileData = async () => {
  if (!props.alumni) {
    profileData.value = null
    employmentData.value = null
    return
  }

  loading.value = true
  error.value = null

  try {
    // Load basic profile data (from props for now)
    profileData.value = props.alumni
    
    // Fetch real-time employment data from employment_records
    await fetchEmploymentData(props.alumni.alumni_id)
    
  } catch (err) {
    console.error('Error loading profile data:', err)
    error.value = 'Failed to load profile information'
  } finally {
    loading.value = false
  }
}

// Watch for changes in alumni prop
watch(
  () => props.alumni,
  (newAlumni) => {
    if (newAlumni) {
      loadProfileData()
    } else {
      profileData.value = null
      employmentData.value = null
    }
  },
  { immediate: true }
)

// Watch for modal show/hide
watch(
  () => props.show,
  (newShow) => {
    if (newShow && props.alumni) {
      loadProfileData()
    }
  }
)
</script>

<style scoped>
/* Custom scrollbar for modal content */
.max-h-\[90vh\]::-webkit-scrollbar {
  width: 8px;
}

.max-h-\[90vh\]::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb {
  background: #2E79BA;
  border-radius: 4px;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb:hover {
  background: #1E549F;
}

/* Smooth transitions */
.transform {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
